<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'status' => 'published',
            'posted_by' => User::where('role', 'admin')->inRandomOrder()->first()?->id ?? 1,
            'is_pinned' => $this->faker->boolean(10),
            'heart_count' => $this->faker->numberBetween(0, 50),
            'like_count' => $this->faker->numberBetween(0, 100),
            'fire_count' => $this->faker->numberBetween(0, 30),
            'sad_count' => $this->faker->numberBetween(0, 10),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
