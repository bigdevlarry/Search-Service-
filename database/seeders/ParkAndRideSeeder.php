<?php

namespace Database\Seeders;

use App\Models\ParkAndRide;
use App\Models\User;
use Illuminate\Database\Seeder;

class ParkAndRideSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::firstOrCreate( // admin user
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        $parkAndRides = [
            [
                'name' => 'Central Station P&R',
                'lat' => 51.5074,
                'lng' => -0.1278,
                'attraction_name' => 'London Eye',
                'location_description' => 'Next to Central Station',
                'minutes_to_destination' => 15,
                'user_id' => $adminUser->id
            ],
            [
                'name' => 'North Mall P&R',
                'lat' => 51.5225,
                'lng' => -0.1522,
                'attraction_name' => 'Oxford Street',
                'location_description' => 'Behind North Mall',
                'minutes_to_destination' => 10,
                'user_id' => $adminUser->id
            ],
            [
                'name' => 'East Park P&R',
                'lat' => 51.5315,
                'lng' => -0.1244,
                'attraction_name' => 'British Museum',
                'location_description' => 'East Park Complex',
                'minutes_to_destination' => 20,
                'user_id' => $adminUser->id
            ]
        ];

        foreach ($parkAndRides as $parkAndRide) {
            ParkAndRide::create($parkAndRide);
        }
    }
}
