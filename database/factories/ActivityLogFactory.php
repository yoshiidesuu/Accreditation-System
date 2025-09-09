<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'log_name' => fake()->randomElement(['default', 'file_access', 'user_activity', 'authentication']),
            'description' => fake()->sentence(),
            'subject_type' => fake()->randomElement([User::class, null]),
            'subject_id' => function (array $attributes) {
                return $attributes['subject_type'] ? 1 : null;
            },
            'event' => fake()->randomElement(['created', 'updated', 'deleted', 'accessed']),
            'causer_type' => User::class,
            'causer_id' => 1, // Use existing user from seeder
            'properties' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ],
            'batch_uuid' => Str::uuid(),
        ];
    }

    /**
     * Indicate that the activity log has no causer.
     */
    public function withoutCauser(): static
    {
        return $this->state(fn (array $attributes) => [
            'causer_type' => null,
            'causer_id' => null,
        ]);
    }

    /**
     * Indicate that the activity log has no subject.
     */
    public function withoutSubject(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject_type' => null,
            'subject_id' => null,
        ]);
    }
}