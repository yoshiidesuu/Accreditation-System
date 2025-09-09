<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\College;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->word(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'parent_area_id' => null,
            'college_id' => College::factory(),
            'academic_year_id' => AcademicYear::factory(),
        ];
    }
}