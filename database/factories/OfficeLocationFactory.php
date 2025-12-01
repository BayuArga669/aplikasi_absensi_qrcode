<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfficeLocation>
 */
class OfficeLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'address' => $this->faker->address,
            'latitude' => $this->faker->latitude(-6.12, -6.27), // Jakarta coordinates
            'longitude' => $this->faker->longitude(106.79, 106.91),
            'radius' => 50, // 50 meters radius
            'is_active' => true,
            'check_in_deadline' => '09:00:00',
            'check_out_deadline' => '17:00:00',
        ];
    }
}
