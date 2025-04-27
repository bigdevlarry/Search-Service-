<?php

namespace App\Http\DTO;

class ParkAndRide
{
    public function __construct(
        public string $attractionName,
        public int $minutesToDestination,
        public string $locationDescription,
    ) {}

    public function toArray(): array
    {
        return [
            'description' => sprintf(
                'Park and Ride to %s. (approx %d minutes to destination)',
                $this->attractionName,
                $this->minutesToDestination
            ),
            'location_name' => $this->locationDescription,
        ];
    }
}
