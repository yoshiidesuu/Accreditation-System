<?php

namespace Database\Factories;

use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccessRequest>
 */
class AccessRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_id' => fake()->uuid(),
            'requester_id' => 1, // Use existing user from seeder
            'reason' => fake()->sentence(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'approver_id' => null,
            'expires_at' => fake()->dateTimeBetween('now', '+30 days'),
            'approved_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'share_link' => null,
            'share_link_expires_at' => null,
        ];
    }

    /**
     * Indicate that the access request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approver_id' => User::factory(),
            'approved_at' => now(),
            'share_link' => fake()->url(),
            'share_link_expires_at' => fake()->dateTimeBetween('now', '+7 days'),
        ]);
    }

    /**
     * Indicate that the access request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approver_id' => User::factory(),
            'rejected_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}