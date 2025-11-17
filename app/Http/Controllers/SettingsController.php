<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSetting;
use App\Services\GeolocationService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $geolocationService;

    public function __construct(GeolocationService $geolocationService)
    {
        $this->geolocationService = $geolocationService;
    }

    /**
     * Display settings page.
     */
    public function index()
    {
        $sites = \App\Models\Site::where('is_active', true)->get();
        $overtimeThreshold = AttendanceSetting::getValue(null, 'overtime_threshold_hours', 10);

        return view('settings.index', compact('sites', 'overtimeThreshold'));
    }

    /**
     * Update geolocation settings for a site.
     */
    public function updateGeolocation(Request $request)
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
            'site_id' => 'required|exists:sites,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:1|max:1000',
        ]);

        $site = \App\Models\Site::findOrFail($validated['site_id']);
        $site->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius' => $validated['radius'],
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Paramètres de géolocalisation mis à jour avec succès.');
    }

    /**
     * Update overtime threshold.
     */
    public function updateOvertimeThreshold(Request $request)
    {
        $validated = $request->validate([
            'overtime_threshold_hours' => 'required|numeric|min:1|max:100',
        ]);

        AttendanceSetting::setValue(null, 'overtime_threshold_hours', (string) $validated['overtime_threshold_hours']);

        return redirect()->route('settings.index')
            ->with('success', 'Seuil d\'heures supplémentaires mis à jour avec succès.');
    }
}
