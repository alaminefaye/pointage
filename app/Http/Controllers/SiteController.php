<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::paginate(15);
        return view('sites.index', compact('sites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sites.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Convertir les virgules en points pour latitude et longitude
        $latitude = str_replace(',', '.', $request->input('latitude'));
        $longitude = str_replace(',', '.', $request->input('longitude'));
        
        // Remplacer les valeurs dans la requête
        $request->merge([
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:1|max:1000',
        ]);

        // Gérer le checkbox is_active (si non coché, il n'est pas dans la requête)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Générer un QR code statique unique pour le site
        do {
            $staticQrCode = 'SITE-' . Str::random(16);
        } while (Site::where('static_qr_code', $staticQrCode)->exists());
        
        $validated['static_qr_code'] = $staticQrCode;

        Site::create($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        // Générer le QR code statique s'il n'existe pas
        if (!$site->static_qr_code) {
            do {
                $staticQrCode = 'SITE-' . Str::random(16);
            } while (Site::where('static_qr_code', $staticQrCode)->exists());
            
            $site->update(['static_qr_code' => $staticQrCode]);
            $site->refresh();
        }
        
        // Générer le QR code en base64 pour l'affichage
        $qrCodeSvg = QrCode::size(300)
            ->margin(2)
            ->generate($site->static_qr_code);
        
        $site->load('attendanceRecords.employee', 'qrCodes');
        return view('sites.show', compact('site', 'qrCodeSvg'));
    }
    
    /**
     * Download QR code as PNG image.
     */
    public function downloadQrCode(Site $site)
    {
        if (!$site->static_qr_code) {
            return redirect()->route('sites.show', $site)
                ->with('error', 'Ce site n\'a pas de QR code.');
        }
        
        $qrCodePng = QrCode::format('png')
            ->size(500)
            ->margin(2)
            ->generate($site->static_qr_code);
        
        $fileName = 'QR-Code-' . Str::slug($site->name) . '-' . $site->id . '.png';
        
        return response($qrCodePng)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        return view('sites.edit', compact('site'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        // Convertir les virgules en points pour latitude et longitude
        $latitude = str_replace(',', '.', $request->input('latitude'));
        $longitude = str_replace(',', '.', $request->input('longitude'));
        
        // Remplacer les valeurs dans la requête
        $request->merge([
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:1|max:1000',
        ]);

        // Gérer le checkbox is_active (si non coché, il n'est pas dans la requête)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $site->update($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Site supprimé avec succès.');
    }
}
