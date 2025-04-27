<?php

namespace App\Services;

use App\ParkAndRide;

class ParkAndRideService
{
    public function searchParkAndRide(array $boundingBox): array
    {
        return ParkAndRide::query()
            ->with('owner')
            ->whereBetween('lat', [$boundingBox['se_lat'], $boundingBox['nw_lat']])
            ->whereBetween('lng', [$boundingBox['nw_lng'], $boundingBox['se_lng']])
            ->get()
            ->keyBy('id')
            ->all();

    }
}
