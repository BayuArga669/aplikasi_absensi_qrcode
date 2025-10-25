<?php

// app/Services/GeolocationService.php
namespace App\Services;

class GeolocationService
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in meters
     */
    public function calculateDistance(
        float $lat1, 
        float $lon1, 
        float $lat2, 
        float $lon2
    ): float {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within allowed radius
     */
    public function isWithinRadius(
        float $userLat,
        float $userLon,
        float $officeLat,
        float $officeLon,
        int $allowedRadius
    ): bool {
        $distance = $this->calculateDistance($userLat, $userLon, $officeLat, $officeLon);
        return $distance <= $allowedRadius;
    }
}