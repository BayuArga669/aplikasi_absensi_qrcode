<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QrCode>
 */
class QrCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->uuid,
            'office_location_id' => \App\Models\OfficeLocation::factory(),
            'generated_at' => now(),
            'expires_at' => now()->addHour(),
            'is_active' => true,
        ];
    }
}
