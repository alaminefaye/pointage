<?php

namespace App\Services;

use App\Models\Site;
use App\Models\AttendanceSetting;

class GeolocationService
{
    /**
     * Calculate distance between two coordinates in meters using Haversine formula.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within allowed zone for a specific site.
     * Vérifie que la position de l'employé est dans le rayon autorisé du site.
     */
    public function isInAllowedZone(int $siteId, float $latitude, float $longitude): bool
    {
        $site = Site::find($siteId);
        
        if (!$site) {
            \Log::warning("Site non trouvé pour l'ID: {$siteId}");
            return false;
        }

        // Vérifier que le site a des coordonnées et un rayon définis
        if (!$site->latitude || !$site->longitude) {
            \Log::warning("Site '{$site->name}' n'a pas de coordonnées GPS définies");
            return false;
        }

        if (!$site->radius || $site->radius <= 0) {
            \Log::warning("Site '{$site->name}' n'a pas de rayon autorisé défini ou rayon invalide: {$site->radius}");
            return false;
        }

        // Calculer la distance entre la position de l'employé et le site
        $distance = $this->calculateDistance(
            (float) $site->latitude,
            (float) $site->longitude,
            $latitude,
            $longitude
        );

        $siteRadius = (float) $site->radius;
        $isInZone = $distance <= $siteRadius;

        // Log pour débogage (peut être désactivé en production)
        \Log::info("Vérification zone - Site: {$site->name}, Distance: {$distance}m, Rayon autorisé: {$siteRadius}m, Dans zone: " . ($isInZone ? 'OUI' : 'NON'));

        return $isInZone;
    }

    /**
     * Get distance from coordinates to site zone in meters.
     */
    public function getDistanceToZone(int $siteId, float $latitude, float $longitude): ?float
    {
        $site = Site::find($siteId);
        
        if (!$site || !$site->latitude || !$site->longitude) {
            return null;
        }

        return $this->calculateDistance(
            (float) $site->latitude,
            (float) $site->longitude,
            $latitude,
            $longitude
        );
    }

    /**
     * Get allowed zone info for a specific site.
     */
    public function getAllowedZone(int $siteId): ?array
    {
        $site = Site::find($siteId);
        
        if (!$site) {
            return null;
        }

        return [
            'latitude' => (float) $site->latitude,
            'longitude' => (float) $site->longitude,
            'radius' => (float) $site->radius,
        ];
    }
}
