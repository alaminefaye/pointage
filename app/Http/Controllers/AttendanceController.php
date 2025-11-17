<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Alert;
use App\Models\Badge;
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
     * Display today's attendance statistics.
     */
    public function today(Request $request)
    {
        $today = Carbon::today();
        
        // Total employés actifs
        $totalEmployees = Employee::where('is_active', true)->count();
        
        // Total qui ont pointé entrée aujourd'hui (distinct employees)
        $checkedIn = AttendanceRecord::whereDate('date', $today)
            ->whereNotNull('check_in_time')
            ->distinct()
            ->count('employee_id');
        
        // Total qui ont pointé sortie aujourd'hui (distinct employees)
        $checkedOut = AttendanceRecord::whereDate('date', $today)
            ->whereNotNull('check_out_time')
            ->distinct()
            ->count('employee_id');
        
        // Total qui sont au repos aujourd'hui
        $onRest = \App\Models\EmployeeRestDay::whereDate('date', $today)
            ->distinct('employee_id')
            ->count('employee_id');
        
        // Récupérer tous les employés actifs avec leurs statuts pour aujourd'hui
        $employeesQuery = Employee::where('is_active', true)
            ->with(['department'])
            ->with(['attendanceRecords' => function($q) use ($today) {
                $q->whereDate('date', $today);
            }])
            ->with(['restDays' => function($q) use ($today) {
                $q->whereDate('date', $today);
            }]);
        
        // Filtres sur les employés
        if ($request->filled('employee_id')) {
            $employeesQuery->where('id', $request->employee_id);
        }
        
        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $employeesQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $allEmployees = $employeesQuery->orderBy('first_name')->get();
        
        // Construire la liste avec les statuts
        $employeeStatuses = [];
        foreach ($allEmployees as $employee) {
            $attendance = $employee->attendanceRecords->first();
            $isRestDay = $employee->restDays->isNotEmpty();
            
            $status = 'none';
            $attendanceRecord = null;
            
            if ($isRestDay) {
                $status = 'rest';
            } elseif ($attendance) {
                if ($attendance->is_absent) {
                    $status = 'absent';
                } elseif ($attendance->check_in_time && $attendance->check_out_time) {
                    $status = 'checked_out';
                } elseif ($attendance->check_in_time) {
                    $status = 'checked_in';
                } else {
                    $status = 'none';
                }
                $attendanceRecord = $attendance;
            } else {
                $status = 'none';
            }
            
            // Filtrer par statut si demandé
            if ($request->filled('status')) {
                if ($request->status === 'checked_in' && $status !== 'checked_in') continue;
                if ($request->status === 'checked_out' && $status !== 'checked_out') continue;
                if ($request->status === 'absent' && $status !== 'absent') continue;
                if ($request->status === 'rest' && $status !== 'rest') continue;
            }
            
            // Filtrer par site si demandé
            if ($request->filled('site_id') && $attendanceRecord && $attendanceRecord->site_id != $request->site_id) {
                continue;
            }
            
            $employeeStatuses[] = [
                'employee' => $employee,
                'status' => $status,
                'attendance' => $attendanceRecord,
            ];
        }
        
        // Pagination manuelle
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = collect($employeeStatuses);
        $total = $items->count();
        $paginatedItems = $items->slice($offset, $perPage)->values();
        
        // Créer un paginator personnalisé
        $employeeStatusesPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        $departments = \App\Models\Department::all();
        $sites = \App\Models\Site::where('is_active', true)->get();
        
        return view('attendance.today', compact(
            'totalEmployees',
            'checkedIn',
            'checkedOut',
            'onRest',
            'employeeStatusesPaginated',
            'today',
            'employees',
            'departments',
            'sites'
        ));
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

        // Vérifier si l'employé a déjà pointé l'entrée aujourd'hui ou hier (travail de nuit)
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        $existingCheckIn = AttendanceRecord::where('employee_id', $employee->id)
            ->where(function($query) use ($today, $yesterday) {
                $query->where('date', $today)
                      ->orWhere(function($q) use ($yesterday) {
                          // Si c'est hier, vérifier qu'il n'y a pas encore de check_out (travail de nuit en cours)
                          $q->where('date', $yesterday)
                            ->whereNotNull('check_in_time')
                            ->whereNull('check_out_time');
                      });
            })
            ->whereNotNull('check_in_time')
            ->first();

        if ($existingCheckIn) {
            // L'employé a déjà pointé l'entrée
            $firstCheckInTime = \Carbon\Carbon::parse($existingCheckIn->check_in_time)->format('H:i');
            $firstCheckInDate = $existingCheckIn->date->format('d/m/Y');
            
            // Créer une alerte pour le double pointage
            Alert::create([
                'employee_id' => $employee->id,
                'type' => 'system',
                'title' => 'Double pointage d\'entrée détecté',
                'message' => "L'employé {$employee->full_name} a tenté de pointer l'entrée deux fois. Premier pointage: {$firstCheckInTime} le {$firstCheckInDate}. Deuxième tentative: " . now()->format('H:i') . " le " . $today->format('d/m/Y') . ". Les deux pointages ne peuvent pas compter pour 8h de travail.",
                'severity' => 'error',
                'metadata' => [
                    'first_attendance_record_id' => $existingCheckIn->id,
                    'first_check_in_time' => $existingCheckIn->check_in_time,
                    'first_check_in_date' => $existingCheckIn->date->format('Y-m-d'),
                    'attempted_check_in_time' => now()->format('H:i:s'),
                ],
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "Vous avez déjà pointé l'entrée à {$firstCheckInTime} le {$firstCheckInDate}. Vous ne pouvez pas pointer deux fois dans la même journée.",
            ], 400);
        }

        // Get or create attendance record for today
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

        // Si le record existe déjà mais sans check_in_time, mettre à jour
        if (!$attendance->check_in_time) {
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

    /**
     * Scan badge QR code for attendance (check-in or check-out).
     * This method is used when an employee scans their badge QR code.
     */
    public function badgeScan(Request $request)
    {
        $validated = $request->validate([
            'badge_qr_code' => 'required|string|max:255',
            'site_qr_code' => 'required|string|max:255', // QR code du site pour validation
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Trouver le badge par son QR code
        $badge = Badge::where('qr_code', $validated['badge_qr_code'])
            ->where('is_active', true)
            ->first();

        if (!$badge) {
            return response()->json([
                'success' => false,
                'message' => 'Badge introuvable ou inactif.',
            ], 404);
        }

        // Vérifier que le badge n'est pas expiré
        if ($badge->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce badge a expiré.',
            ], 403);
        }

        $employee = $badge->employee;

        // Vérifier que l'employé est actif
        if (!$employee->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'L\'employé associé à ce badge n\'est pas actif.',
            ], 403);
        }

        // Valider le QR code du site
        $siteQrCode = $this->qrCodeService->validateAndUseQrCode($validated['site_qr_code'], $employee->id);
        if (!$siteQrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR code du site invalide ou expiré.',
            ], 400);
        }

        // Vérifier la géolocalisation si fournie
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $isInZone = $this->geolocationService->isInAllowedZone(
                $siteQrCode->site_id,
                $validated['latitude'],
                $validated['longitude']
            );

            if (!$isInZone) {
                $site = \App\Models\Site::find($siteQrCode->site_id);
                $distance = $this->geolocationService->getDistanceToZone(
                    $siteQrCode->site_id,
                    $validated['latitude'],
                    $validated['longitude']
                );
                
                $message = 'Vous êtes hors de la zone autorisée. Pointage bloqué.';
                if ($distance !== null && $site) {
                    if ($distance < 1000) {
                        $message = sprintf(
                            'Vous êtes à %d mètres du site (zone autorisée: %d mètres). Pointage bloqué.',
                            (int) $distance,
                            (int) $site->radius
                        );
                    } else {
                        $distanceKm = round($distance / 1000, 2);
                        $radiusKm = round($site->radius / 1000, 2);
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
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Vérifier le statut actuel de l'employé
        $existingAttendance = AttendanceRecord::where('employee_id', $employee->id)
            ->where(function($query) use ($today, $yesterday) {
                $query->where('date', $today)
                      ->orWhere(function($q) use ($yesterday) {
                          $q->where('date', $yesterday)
                            ->whereNotNull('check_in_time')
                            ->whereNull('check_out_time');
                      });
            })
            ->first();

        // Déterminer si c'est un check-in ou check-out
        if ($existingAttendance && $existingAttendance->check_in_time && !$existingAttendance->check_out_time) {
            // Check-out
            // Vérifier si c'est un travail de nuit (check-in hier, check-out aujourd'hui)
            $attendanceDate = $existingAttendance->date;
            if ($attendanceDate->isYesterday()) {
                // C'est un check-out pour un travail de nuit
                $existingAttendance->update([
                    'check_out_time' => now()->format('H:i:s'),
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'is_in_zone' => $request->filled('latitude') && $request->filled('longitude'),
                    'qr_code_used' => $validated['site_qr_code'],
                ]);
            } else {
                // Check-out normal
                $existingAttendance->update([
                    'check_out_time' => now()->format('H:i:s'),
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'is_in_zone' => $request->filled('latitude') && $request->filled('longitude'),
                    'qr_code_used' => $validated['site_qr_code'],
                ]);
            }

            // Calculer l'attendance
            $this->calculationService->calculateAttendance($existingAttendance->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Pointage de sortie enregistré avec succès.',
                'type' => 'check_out',
                'attendance' => $existingAttendance->fresh(),
            ]);
        } else {
            // Check-in
            // Vérifier si l'employé a déjà pointé l'entrée aujourd'hui ou hier
            $existingCheckIn = AttendanceRecord::where('employee_id', $employee->id)
                ->where(function($query) use ($today, $yesterday) {
                    $query->where('date', $today)
                          ->orWhere(function($q) use ($yesterday) {
                              $q->where('date', $yesterday)
                                ->whereNotNull('check_in_time')
                                ->whereNull('check_out_time');
                          });
                })
                ->whereNotNull('check_in_time')
                ->first();

            if ($existingCheckIn) {
                $firstCheckInTime = \Carbon\Carbon::parse($existingCheckIn->check_in_time)->format('H:i');
                $firstCheckInDate = $existingCheckIn->date->format('d/m/Y');
                
                Alert::create([
                    'employee_id' => $employee->id,
                    'type' => 'system',
                    'title' => 'Double pointage d\'entrée détecté',
                    'message' => "L'employé {$employee->full_name} a tenté de pointer l'entrée deux fois via badge. Premier pointage: {$firstCheckInTime} le {$firstCheckInDate}.",
                    'severity' => 'error',
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "Vous avez déjà pointé l'entrée à {$firstCheckInTime} le {$firstCheckInDate}.",
                ], 400);
            }

            // Créer ou mettre à jour l'enregistrement de pointage
            $attendance = AttendanceRecord::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $today,
                ],
                [
                    'site_id' => $siteQrCode->site_id,
                    'check_in_time' => now()->format('H:i:s'),
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'is_in_zone' => $request->filled('latitude') && $request->filled('longitude'),
                    'qr_code_used' => $validated['site_qr_code'],
                ]
            );

            if (!$attendance->check_in_time) {
                $attendance->update([
                    'site_id' => $siteQrCode->site_id,
                    'check_in_time' => now()->format('H:i:s'),
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'is_in_zone' => $request->filled('latitude') && $request->filled('longitude'),
                    'qr_code_used' => $validated['site_qr_code'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pointage d\'entrée enregistré avec succès.',
                'type' => 'check_in',
                'attendance' => $attendance->fresh(),
            ]);
        }
    }

    /**
     * Display badge scanner page for admins.
     * Allows admins to scan employee badges to mark attendance.
     */
    public function badgeScanner()
    {
        $sites = \App\Models\Site::where('is_active', true)->get();
        return view('attendance.badge-scanner', compact('sites'));
    }

    /**
     * Process badge scan by admin.
     * Scans employee badge and automatically marks check-in or check-out.
     */
    public function scanBadgeByAdmin(Request $request)
    {
        $validated = $request->validate([
            'badge_qr_code' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
        ]);

        // Trouver le badge par son QR code
        $badge = Badge::where('qr_code', $validated['badge_qr_code'])
            ->where('is_active', true)
            ->first();

        if (!$badge) {
            return response()->json([
                'success' => false,
                'message' => 'Badge introuvable ou inactif.',
            ], 404);
        }

        // Vérifier que le badge n'est pas expiré
        if ($badge->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce badge a expiré.',
            ], 403);
        }

        $employee = $badge->employee;

        // Vérifier que l'employé est actif
        if (!$employee->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'L\'employé associé à ce badge n\'est pas actif.',
            ], 403);
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Vérifier le statut actuel de l'employé
        $existingAttendance = AttendanceRecord::where('employee_id', $employee->id)
            ->where(function($query) use ($today, $yesterday) {
                $query->where('date', $today)
                      ->orWhere(function($q) use ($yesterday) {
                          $q->where('date', $yesterday)
                            ->whereNotNull('check_in_time')
                            ->whereNull('check_out_time');
                      });
            })
            ->first();

        // Déterminer si c'est un check-in ou check-out
        if ($existingAttendance && $existingAttendance->check_in_time && !$existingAttendance->check_out_time) {
            // Check-out
            $attendanceDate = $existingAttendance->date;
            if ($attendanceDate->isYesterday()) {
                // C'est un check-out pour un travail de nuit
                $existingAttendance->update([
                    'check_out_time' => now()->format('H:i:s'),
                    'site_id' => $validated['site_id'],
                ]);
            } else {
                // Check-out normal
                $existingAttendance->update([
                    'check_out_time' => now()->format('H:i:s'),
                    'site_id' => $validated['site_id'],
                ]);
            }

            // Calculer l'attendance
            $this->calculationService->calculateAttendance($existingAttendance->fresh());

            return response()->json([
                'success' => true,
                'message' => "Pointage de sortie enregistré pour {$employee->full_name}.",
                'type' => 'check_out',
                'employee' => $employee,
                'attendance' => $existingAttendance->fresh(),
            ]);
        } else {
            // Check-in
            // Vérifier si l'employé a déjà pointé l'entrée aujourd'hui ou hier
            $existingCheckIn = AttendanceRecord::where('employee_id', $employee->id)
                ->where(function($query) use ($today, $yesterday) {
                    $query->where('date', $today)
                          ->orWhere(function($q) use ($yesterday) {
                              $q->where('date', $yesterday)
                                ->whereNotNull('check_in_time')
                                ->whereNull('check_out_time');
                          });
                })
                ->whereNotNull('check_in_time')
                ->first();

            if ($existingCheckIn) {
                $firstCheckInTime = Carbon::parse($existingCheckIn->check_in_time)->format('H:i');
                $firstCheckInDate = $existingCheckIn->date->format('d/m/Y');
                
                Alert::create([
                    'employee_id' => $employee->id,
                    'type' => 'system',
                    'title' => 'Double pointage d\'entrée détecté',
                    'message' => "L'employé {$employee->full_name} a tenté de pointer l'entrée deux fois via badge. Premier pointage: {$firstCheckInTime} le {$firstCheckInDate}.",
                    'severity' => 'error',
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "{$employee->full_name} a déjà pointé l'entrée à {$firstCheckInTime} le {$firstCheckInDate}.",
                ], 400);
            }

            // Créer ou mettre à jour l'enregistrement de pointage
            $attendance = AttendanceRecord::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $today,
                ],
                [
                    'site_id' => $validated['site_id'],
                    'check_in_time' => now()->format('H:i:s'),
                ]
            );

            if (!$attendance->check_in_time) {
                $attendance->update([
                    'site_id' => $validated['site_id'],
                    'check_in_time' => now()->format('H:i:s'),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Pointage d'entrée enregistré pour {$employee->full_name}.",
                'type' => 'check_in',
                'employee' => $employee,
                'attendance' => $attendance->fresh(),
            ]);
        }
    }
}
