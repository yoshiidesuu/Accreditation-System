<?php

namespace Database\Factories;

use App\Models\College;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\College>
 */
class CollegeFactory extends Factory
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
            'code' => fake()->unique()->regexify('[A-Z]{3}'),
            'address' => fake()->address(),
            'contact' => fake()->phoneNumber(),
            'coordinator_id' => 1, // Use existing user from seeder
            'meta' => [],
        ];
    }
}