<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\GeolocationService;
use App\Services\QrCodeService;
use App\Services\AttendanceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $geolocationService;
    protected $qrCodeService;
    protected $calculationService;

    public function __construct(
        GeolocationService $geolocationService,
        QrCodeService $qrCodeService,
        AttendanceCalculationService $calculationService
    ) {
        $this->geolocationService = $geolocationService;
        $this->qrCodeService = $qrCodeService;
        $this->calculationService = $calculationService;
    }

    /**
     * Display attendance records.
     */
    public function index(Request $request)
    {
        $query = AttendanceRecord::with('employee.department', 'site');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $records = $query->orderBy('date', 'desc')->paginate(20);
        $employees = Employee::where('is_active', true)->get();
        $sites = \App\Models\Site::where('is_active', true)->get();

        return view('attendance.index', compact('records', 'employees', 'sites'));
    }

    /**
     * Check in employee.
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'qr_code' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // Validate QR code
        $qrCode = $this->qrCodeService->validateAndUseQrCode($validated['qr_code'], $employee->id);
        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR code invalide ou expiré.',
            ], 400);
        }

        // Check geolocation using the site from QR code
        $isInZone = $this->geolocationService->isInAllowedZone(
            $qrCode->site_id,
            $validated['latitude'],
            $validated['longitude']
        );

        if (!$isInZone) {
            $site = \App\Models\Site::find($qrCode->site_id);
            $distance = $this->geolocationService->getDistanceToZone(
                $qrCode->site_id,
                $validated['latitude'],
                $validated['longitude']
            );
            
            $message = 'Vous êtes hors de la zone autorisée. Pointage bloqué.';
            if ($distance !== null && $site) {
                $distanceKm = round($distance / 1000, 2);
                $radiusKm = round($site->radius / 1000, 2);
                if ($distance < 1000) {
                    $message = sprintf(
                        'Vous êtes à %d mètres du site (zone autorisée: %d mètres). Pointage bloqué.',
                        (int) $distance,
                        (int) $site->radius
                    );
                } else {
                    $message = sprintf(
                        'Vous êtes à %.2f km du site (zone autorisée: %.2f km). Pointage bloqué.',
                        $distanceKm,
                        $radiusKm
                    );
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        // Get or create attendance record for today
        $today = Carbon::today();
        $attendance = AttendanceRecord::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $today,
            ],
            [
                'site_id' => $qrCode->site_id,
                'check_in_time' => now()->format('H:i:s'),
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'is_in_zone' => true,
                'qr_code_used' => $validated['qr_code'],
            ]
        );

        // If already checked in, update check in time
        if ($attendance->check_in_time) {
            $attendance->update([
                'site_id' => $qrCode->site_id,
                'check_in_time' => now()->format('H:i:s'),
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'is_in_zone' => true,
                'qr_code_used' => $validated['qr_code'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pointage d\'entrée enregistré avec succès.',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Check out employee.
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'qr_code' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // Validate QR code
        $qrCode = $this->qrCodeService->validateAndUseQrCode($validated['qr_code'], $employee->id);
        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR code invalide ou expiré.',
            ], 400);
        }

        // Check geolocation using the site from QR code
        $isInZone = $this->geolocationService->isInAllowedZone(
            $qrCode->site_id,
            $validated['latitude'],
            $validated['longitude']
        );

        if (!$isInZone) {
            $site = \App\Models\Site::find($qrCode->site_id);
            $distance = $this->geolocationService->getDistanceToZone(
                $qrCode->site_id,
                $validated['latitude'],
                $validated['longitude']
            );
            
            $message = 'Vous êtes hors de la zone autorisée. Pointage bloqué.';
            if ($distance !== null && $site) {
                $distanceKm = round($distance / 1000, 2);
                $radiusKm = round($site->radius / 1000, 2);
                if ($distance < 1000) {
                    $message = sprintf(
                        'Vous êtes à %d mètres du site (zone autorisée: %d mètres). Pointage bloqué.',
                        (int) $distance,
                        (int) $site->radius
                    );
                } else {
                    $message = sprintf(
                        'Vous êtes à %.2f km du site (zone autorisée: %.2f km). Pointage bloqué.',
                        $distanceKm,
                        $radiusKm
                    );
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        // Chercher le pointage d'entrée (peut être aujourd'hui ou hier si travail de nuit)
        // On cherche d'abord aujourd'hui, puis hier si pas trouvé
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        $attendance = AttendanceRecord::where('employee_id', $employee->id)
            ->where(function($query) use ($today, $yesterday) {
                $query->where('date', $today)
                      ->orWhere('date', $yesterday);
            })
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->orderBy('date', 'desc')
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun pointage d\'entrée trouvé (aujourd\'hui ou hier).',
            ], 400);
        }

        // Update check out
        // Le check_out_time est enregistré tel quel (même si c'est le lendemain)
        // Le calcul gérera automatiquement le passage à minuit
        $attendance->update([
            'site_id' => $qrCode->site_id,
            'check_out_time' => now()->format('H:i:s'),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'is_in_zone' => true,
            'qr_code_used' => $validated['qr_code'],
        ]);

        // Calculate attendance
        $this->calculationService->calculateAttendance($attendance);

        return response()->json([
            'success' => true,
            'message' => 'Pointage de sortie enregistré avec succès.',
            'attendance' => $attendance->fresh(),
        ]);
    }

    /**
     * Get employee's today attendance status.
     */
    public function getTodayStatus(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Chercher le pointage d'aujourd'hui ou d'hier (si travail de nuit)
        $attendance = AttendanceRecord::where('employee_id', $employeeId)
            ->where(function($query) use ($today, $yesterday) {
                $query->where('date', $today)
                      ->orWhere(function($q) use ($yesterday) {
                          // Si c'est hier, vérifier qu'il n'y a pas encore de check_out (travail de nuit en cours)
                          $q->where('date', $yesterday)
                            ->whereNotNull('check_in_time')
                            ->whereNull('check_out_time');
                      });
            })
            ->orderBy('date', 'desc')
            ->first();

        return response()->json([
            'attendance' => $attendance,
            'has_checked_in' => $attendance && $attendance->check_in_time ? true : false,
            'has_checked_out' => $attendance && $attendance->check_out_time ? true : false,
        ]);
    }
}
