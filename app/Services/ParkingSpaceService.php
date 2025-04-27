<?php

namespace App\Services;

use App\ParkingSpace;

class ParkingSpaceService
{
    public function searchParkingSpaces(array $boundingBox): array
    {
        return ParkingSpace::query()
            ->with('owner')
            ->whereBetween('lat', [$boundingBox['se_lat'], $boundingBox['nw_lat']])
            ->whereBetween('lng', [$boundingBox['nw_lng'], $boundingBox['se_lng']])
            ->get()
            ->keyBy('id')
            ->all();
    }
}
