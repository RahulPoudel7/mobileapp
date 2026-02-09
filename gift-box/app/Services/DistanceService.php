<?php

namespace App\Services;

use App\Exceptions\DistanceException;

class DistanceService
{
    protected $storeLat;
    protected $storeLong;

    public function __construct()
    {
        // Load store coordinates from .env
        $this->storeLat = (float) env('STORE_LATITUDE');
        $this->storeLong = (float) env('STORE_LONGITUDE');
    }

    /**
     * Calculate distance using Haversine formula (No API required)
     */
    public function getDistanceInKm($lat, $lng): float
    {
        if (!$this->storeLat || !$this->storeLong) {
            throw new DistanceException("Store coordinates are not configured in .env");
        }

        $earthRadius = 6371; // Radius of earth in km

        $dLat = deg2rad($lat - $this->storeLat);
        $dLon = deg2rad($lng - $this->storeLong);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($this->storeLat)) * cos(deg2rad($lat)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2); // Return distance rounded to 2 decimal places
    }
}