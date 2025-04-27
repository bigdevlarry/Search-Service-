<?php

namespace App\Services;

class SearchService
{
    const WGS84_A = 6378137.0; // Major semiaxis
    const WGS84_B = 6356752.3; // Major semiaxis

    public function getBoundingBox(float $latitude, float $longitude, int $radius): array
    {
        $latitude = deg2rad($latitude);
        $longitude = deg2rad($longitude);
        $halfSide = 1000 * $radius;

        $radius = $this->calculateWGS84EarthRadius($latitude);
        $pRadius = $radius * cos($latitude);

        return [
            'se_lat' => rad2deg($latitude - $halfSide/$radius),
            'nw_lat' => rad2deg($latitude + $halfSide/$radius),
            'nw_lng' => rad2deg($longitude - $halfSide/$pRadius),
            'se_lng' => rad2deg($longitude + $halfSide/$pRadius),
        ];
    }

    /**
     * Calculate Earth radius at a given latitude, according to the WGS-84 ellipsoid [m]
     */
    private function calculateWGS84EarthRadius(float $latitude): float
    {
        $An = self::WGS84_A * self::WGS84_A * cos($latitude);
        $Bn = self::WGS84_B * self::WGS84_B * sin($latitude);
        $Ad = self::WGS84_A * cos($latitude);
        $Bd = self::WGS84_B * sin($latitude);
        return sqrt(($An * $An + $Bn * $Bn) / ($Ad * $Ad + $Bd * $Bd));
    }
}
