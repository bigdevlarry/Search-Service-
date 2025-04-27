<?php

namespace Database\Factories;

use App\Models\User;
use App\ParkAndRide;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParkAndRideFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ParkAndRide::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'user_id' => User::factory()->create(),
            'attraction_name' => $this->faker->word,
            'location_description' => $this->faker->sentence,
            'minutes_to_destination' => $this->faker->randomNumber(),
        ];
    }
}
