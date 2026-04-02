<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'title'     => fake()->words(3, true),
            'url'       => fake()->url(),
            'icon'      => '🔗',
            'is_active' => true,
            'order'     => fake()->numberBetween(1, 100),
        ];
    }
}
