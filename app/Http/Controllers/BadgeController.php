<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

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
     * Download badge as PNG image.
     * Uses HTML to image conversion approach via a dedicated view.
     */
    public function downloadQrCode(Badge $badge)
    {
        $badge->load('employee.department');
        
        // Générer le QR code en PNG pour l'intégration
        $qrCodePng = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($badge->qr_code);
        
        // Créer une image composite avec GD
        // Dimensions: 85.6mm x 53.98mm à 300 DPI = 1011px x 637px
        // On utilise 1000px x 630px pour un bon ratio
        $width = 1000;
        $height = 630;
        
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        
        // Couleurs
        $greenDark = imagecolorallocate($image, 7, 65, 54); // #074136
        $greenLight = imagecolorallocate($image, 10, 90, 74); // #0a5a4a
        $white = imagecolorallocate($image, 255, 255, 255);
        $whiteSemi = imagecolorallocatealpha($image, 255, 255, 255, 50);
        $textWhite = imagecolorallocate($image, 255, 255, 255);
        $textDark = imagecolorallocate($image, 7, 65, 54);
        
        // Fond vert (tout le badge est vert)
        imagefilledrectangle($image, 0, 0, $width, $height, $greenDark);
        
        // Logo GASPARD SIGNATURE (en haut à gauche)
        imagestring($image, 5, 30, 20, 'GASPARD', $textWhite);
        imagestring($image, 3, 30, 45, 'SIGNATURE', $textWhite);
        
        // Numéro de badge (en haut à droite)
        $badgeNumberText = '#' . $badge->badge_number;
        $badgeNumberWidth = imagefontwidth(3) * strlen($badgeNumberText) + 20;
        $badgeNumberX = $width - $badgeNumberWidth - 20;
        // Fond semi-transparent pour le numéro
        imagefilledrectangle($image, $badgeNumberX - 10, 25, $badgeNumberX + $badgeNumberWidth, 50, $whiteSemi);
        imagestring($image, 3, $badgeNumberX, 30, $badgeNumberText, $textDark);
        
        // Nom de l'employé (en majuscules, dans le body)
        $bodyY = 80;
        $employeeName = strtoupper($badge->employee->full_name);
        imagestring($image, 5, 30, $bodyY, $employeeName, $textWhite);
        
        // Détails employé
        $detailY = $bodyY + 50;
        $lineHeight = 30;
        
        $codeText = 'Code: ' . $badge->employee->employee_code;
        imagestring($image, 3, 30, $detailY, $codeText, $textWhite);
        
        if ($badge->employee->position) {
            $posteText = 'Poste: ' . $badge->employee->position;
            imagestring($image, 3, 30, $detailY + $lineHeight, $posteText, $textWhite);
        }
        
        if ($badge->employee->department) {
            $deptText = 'Dept: ' . $badge->employee->department->name;
            $deptY = $detailY + ($badge->employee->position ? $lineHeight * 2 : $lineHeight);
            imagestring($image, 3, 30, $deptY, $deptText, $textWhite);
        }
        
        // QR Code (à droite, dans le body vert)
        $qrCodeImage = imagecreatefromstring($qrCodePng);
        $qrSize = 140; // Taille du QR code dans le badge
        $qrX = $width - $qrSize - 40;
        $qrY = $bodyY + 20;
        
        // Fond blanc pour le QR code
        imagefilledrectangle($image, $qrX - 5, $qrY - 5, $qrX + $qrSize + 5, $qrY + $qrSize + 5, $white);
        
        // Copier le QR code
        imagecopyresampled($image, $qrCodeImage, $qrX, $qrY, 0, 0, $qrSize, $qrSize, imagesx($qrCodeImage), imagesy($qrCodeImage));
        imagedestroy($qrCodeImage);
        
        // Texte "SCAN ME" sous le QR code
        $scanText = 'SCAN ME';
        $scanTextWidth = imagefontwidth(2) * strlen($scanText);
        $scanTextX = $qrX + ($qrSize / 2) - ($scanTextWidth / 2);
        imagestring($image, 2, $scanTextX, $qrY + $qrSize + 8, $scanText, $textWhite);
        
        // Footer - Département et Validité
        $footerY = $height - 40;
        $departmentName = strtoupper($badge->employee->department->name ?? 'N/A');
        imagestring($image, 3, 30, $footerY, $departmentName, $textWhite);
        
        $validityText = $badge->expires_at 
            ? 'Valide jusqu\'au ' . $badge->expires_at->format('m/Y')
            : 'Valide indefiniment';
        $validityWidth = imagefontwidth(2) * strlen($validityText);
        imagestring($image, 2, $width - $validityWidth - 30, $footerY, $validityText, $textWhite);
        
        // Output
        ob_start();
        imagepng($image, null, 9); // Qualité maximale
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        
        $fileName = 'Badge-' . $badge->badge_number . '-' . $badge->id . '.png';
        
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
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
