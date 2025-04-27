<?php

namespace App\Http\DTO;

class ParkingSpace
{
    public function __construct(
        public int $noOfSpaces,
        public string $spaceDetails,
        public string $streetName,
        public string $city,
    ) {}

    public function toArray(): array
    {
        return [
            'description' => sprintf(
                'Parking space with %d bays: %s',
                $this->noOfSpaces,
                $this->spaceDetails
            ),
            'location_name' => sprintf('%s, %s', $this->streetName, $this->city),
        ];
    }
}
