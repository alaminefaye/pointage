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
     */
    public function isInAllowedZone(int $siteId, float $latitude, float $longitude): bool
    {
        $site = Site::find($siteId);
        
        if (!$site) {
            return false;
        }

        $distance = $this->calculateDistance(
            $site->latitude,
            $site->longitude,
            $latitude,
            $longitude
        );

        return $distance <= $site->radius;
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
