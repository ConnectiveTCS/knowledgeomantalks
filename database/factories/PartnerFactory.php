<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    // ...existing code...

    public function definition(): array
    {
        return [
            // ...existing code...
            'partnership_renewal_approval' => $this->faker->randomElement(['approved', 'rejected', 'pending']),
            // ...existing code...
        ];
    }
}
