<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'url' => fake()->url(),
            'icon' => '🔗',
            'is_active' => true,
            'order' => fake()->numberBetween(1, 100),
        ];
    }

    public function configure(): static
    {
        // Runs after state resolution but BEFORE the DB insert, so profile_id is set in time.
        return $this->afterMaking(function ($link) {
            if (! $link->profile_id && $link->user_id) {
                $link->profile_id = Profile::where('user_id', $link->user_id)
                    ->where('is_default', true)
                    ->value('id')
                    ?? Profile::where('user_id', $link->user_id)->value('id');
            }
        });
    }
}
