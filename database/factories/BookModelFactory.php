<?php

namespace Database\Factories;

use App\Models\BookModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookModel>
 */
class BookModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Book ' . \Illuminate\Support\Str::random(10),
            'desc' => 'Description ' . \Illuminate\Support\Str::random(100),
        ];
    }
}
