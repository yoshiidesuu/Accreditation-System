<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2020, 2025);
        $endYear = $startYear + 1;
        
        return [
            'label' => $startYear . '-' . $endYear,
            'start_date' => Carbon::create($startYear, 8, 1), // August 1st
            'end_date' => Carbon::create($endYear, 7, 31), // July 31st next year
            'active' => false,
        ];
    }

    /**
     * Indicate that the academic year is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indicate that the academic year is current (2024-2025).
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'label' => '2024-2025',
            'start_date' => Carbon::create(2024, 8, 1),
            'end_date' => Carbon::create(2025, 7, 31),
            'active' => true,
        ]);
    }
}