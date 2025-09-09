<?php

namespace Database\Factories;

use App\Models\Parameter;
use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parameter>
 */
class ParameterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->regexify('[A-D][1-9]'),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'subcategory' => fake()->optional()->regexify('[1-9]\.[1-9]'),
            'weight' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['active', 'inactive', 'draft']),
            'required_documents' => json_encode([]),
            'evaluation_criteria' => fake()->paragraph(),
            'created_by' => 1, // Use existing user from seeder
            'updated_by' => null,
        ];
    }
}