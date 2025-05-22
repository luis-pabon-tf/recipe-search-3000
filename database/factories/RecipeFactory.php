<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        switch (rand(1,3)) {
            case 1:
                $name = fake()->country() . ' Smoothie';
                break;
            case 2:
                $name = 'Potatoes for ' . fake()->firstName();
                break;
            default:
                $name = 'Meat';
        }

        return [
            'name' => $name,
            'description' => fake()->text(),
            'slug' => Str::slug($name),
        ];
    }
}
