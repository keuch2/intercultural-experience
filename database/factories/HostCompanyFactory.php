<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HostCompany>
 */
class HostCompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'industry' => fake()->randomElement(['Hospitality', 'Retail', 'Tourism', 'Food Service', 'Entertainment']),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'USA',
            'address' => fake()->streetAddress(),
            'contact_person' => fake()->name(),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'rating' => fake()->randomFloat(1, 3.0, 5.0),
            'notes' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
