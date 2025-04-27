<?php

namespace App;

class SearchService
{
    const WGS84_A = 6378137.0; // Major semiaxis
    const WGS84_B = 6356752.3; // Major semiaxis

    public function getBoundingBox(float $lat, float $lng, int $radius): array
    {
        $lat = deg2rad($lat);
        $lng = deg2rad($lng);
        $halfSide = 1000 * $radius;

        $radius = $this->calculateWGS84EarthRadius($lat);
        $pRadius = $radius * cos($lat);

        return [
            'se_lat' => rad2deg($lat - $halfSide/$radius),
            'nw_lat' => rad2deg($lat + $halfSide/$radius),
            'nw_lng' => rad2deg($lng - $halfSide/$pRadius),
            'se_lng' => rad2deg($lng + $halfSide/$pRadius),
        ];
    }

    /**
     * Calculate Earth radius at a given latitude, according to the WGS-84 ellipsoid [m]
     */
    private function calculateWGS84EarthRadius(float $lat): float
    {
        $An = self::WGS84_A * self::WGS84_A * cos($lat);
        $Bn = self::WGS84_B * self::WGS84_B * sin($lat);
        $Ad = self::WGS84_A * cos($lat);
        $Bd = self::WGS84_B * sin($lat);
        return sqrt(($An * $An + $Bn * $Bn) / ($Ad * $Ad + $Bd * $Bd));
    }
}
