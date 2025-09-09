<?php

namespace Database\Factories;

use App\Models\ParameterContent;
use App\Models\Parameter;
use App\Models\User;
use App\Models\College;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParameterContent>
 */
class ParameterContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parameter_id' => Parameter::factory(),
            'uploaded_by' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'content_type' => fake()->randomElement(['document', 'image', 'video', 'link', 'text']),
            'file_path' => fake()->optional()->filePath(),
            'file_name' => fake()->optional()->word() . '.pdf',
            'file_size' => fake()->optional()->numberBetween(1024, 10485760),
            'mime_type' => fake()->optional()->mimeType(),
            'content' => fake()->paragraph(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'revision_needed']),
            'review_notes' => fake()->optional()->sentence(),
            'reviewed_by' => null,
            'reviewed_at' => fake()->optional()->dateTime(),
            'version' => 1,
            'is_current_version' => true,
        ];
    }

    /**
     * Indicate that the parameter content is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);
    }

    /**
     * Indicate that the parameter content is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'submitted_at' => fake()->dateTime(),
        ]);
    }

    /**
     * Indicate that the parameter content is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'submitted_at' => fake()->dateTime(),
            'reviewed_at' => fake()->dateTime(),
            'reviewed_by' => User::factory(),
        ]);
    }
}