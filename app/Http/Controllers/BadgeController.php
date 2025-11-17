<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BadgeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Badge::with('employee.department');
        
        // Recherche par numéro de badge, code QR ou nom d'employé
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('badge_number', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($empQuery) use ($search) {
                      $empQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Filtre par employé
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        $badges = $query->orderBy('created_at', 'desc')->paginate(20);
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        
        return view('badges.index', compact('badges', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        return view('badges.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'badge_number' => 'required|string|max:255|unique:badges,badge_number',
            'notes' => 'nullable|string',
            'issued_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:issued_at',
        ]);
        
        // Générer un code QR unique pour le badge
        do {
            $qrCode = 'BADGE-' . Str::random(20);
        } while (Badge::where('qr_code', $qrCode)->exists());
        
        $validated['qr_code'] = $qrCode;
        $validated['is_active'] = $request->has('is_active') ? true : true;
        
        if (!isset($validated['issued_at'])) {
            $validated['issued_at'] = Carbon::today();
        }
        
        Badge::create($validated);
        
        return redirect()->route('badges.index')
            ->with('success', 'Badge créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Badge $badge)
    {
        $badge->load('employee.department');
        
        // Générer le QR code en SVG pour l'affichage (même taille que print)
        $qrCodeSvg = QrCode::size(200)
            ->margin(1)
            ->generate($badge->qr_code);
        
        return view('badges.show', compact('badge', 'qrCodeSvg'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Badge $badge)
    {
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        return view('badges.edit', compact('badge', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'badge_number' => 'required|string|max:255|unique:badges,badge_number,' . $badge->id,
            'notes' => 'nullable|string',
            'issued_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:issued_at',
        ]);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        $badge->update($validated);
        
        return redirect()->route('badges.index')
            ->with('success', 'Badge mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Badge $badge)
    {
        $badge->delete();
        
        return redirect()->route('badges.index')
            ->with('success', 'Badge supprimé avec succès.');
    }

    /**
     * Download badge as PDF.
     * Uses the same HTML/CSS as print view for consistent design.
     */
    public function downloadQrCode(Badge $badge)
    {
        $badge->load('employee.department');
        
        // Générer le QR code en PNG base64 pour le PDF (DomPDF supporte mieux PNG que SVG)
        $qrCodePng = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($badge->qr_code);
        
        $qrCodeBase64 = base64_encode($qrCodePng);
        
        // Générer le PDF avec le même design que print.blade.php
        $pdf = Pdf::loadView('badges.badge-pdf', compact('badge', 'qrCodeBase64'));
        
        // Utiliser A4 et centrer le badge (85.6mm x 53.98mm = taille d'une carte de crédit)
        $pdf->setPaper('a4', 'portrait');
        
        $fileName = 'Badge-' . $badge->badge_number . '-' . $badge->id . '.pdf';
        
        return $pdf->download($fileName);
    }

    /**
     * Toggle badge active status.
     */
    public function toggleStatus(Badge $badge)
    {
        $badge->update(['is_active' => !$badge->is_active]);
        
        $status = $badge->is_active ? 'activé' : 'désactivé';
        
        return redirect()->route('badges.index')
            ->with('success', "Badge {$status} avec succès.");
    }

    /**
     * Display printable badge design.
     */
    public function print(Badge $badge)
    {
        $badge->load('employee.department');
        
        // Générer le QR code en SVG pour l'affichage
        $qrCodeSvg = QrCode::size(200)
            ->margin(1)
            ->generate($badge->qr_code);
        
        return view('badges.print', compact('badge', 'qrCodeSvg'));
    }
}
