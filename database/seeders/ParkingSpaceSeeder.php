<?php

namespace Database\Seeders;

use App\Models\ParkingSpace;
use App\Models\User;
use Illuminate\Database\Seeder;

class ParkingSpaceSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        $parkingSpaces = [
            [
                'name' => 'Oxford Street Parking',
                'lat' => 51.5152,
                'lng' => -0.1418,
                'space_details' => 'Underground parking with security',
                'city' => 'London',
                'street_name' => 'Oxford Street',
                'no_of_spaces' => 50,
                'user_id' => $adminUser->id
            ],
            [
                'name' => 'Piccadilly Garage',
                'lat' => 51.5102,
                'lng' => -0.1345,
                'space_details' => 'Street level parking',
                'city' => 'London',
                'street_name' => 'Piccadilly',
                'no_of_spaces' => 25,
                'user_id' => $adminUser->id
            ],
            [
                'name' => 'Camden Lock Parking',
                'lat' => 51.5415,
                'lng' => -0.1466,
                'space_details' => 'Outdoor parking lot',
                'city' => 'London',
                'street_name' => 'Camden High Street',
                'no_of_spaces' => 35,
                'user_id' => $adminUser->id
            ],
            [
                'name' => 'Liverpool Street Station Parking',
                'lat' => 51.5178,
                'lng' => -0.0817,
                'space_details' => 'Indoor multi-story car park',
                'city' => 'London',
                'street_name' => 'Liverpool Street',
                'no_of_spaces' => 100,
                'user_id' => $adminUser->id
            ],
            [
                'name' => 'Covent Garden Parking',
                'lat' => 51.5129,
                'lng' => -0.1243,
                'space_details' => 'Underground secure parking',
                'city' => 'London',
                'street_name' => 'Covent Garden',
                'no_of_spaces' => 45,
                'user_id' => $adminUser->id
            ]
        ];

        foreach ($parkingSpaces as $parkingSpace) {
            ParkingSpace::create($parkingSpace);
        }
    }
}
