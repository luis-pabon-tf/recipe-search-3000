<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recipe;

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
                $name = fake()->city() . 'Meat Dish';
        }

        return [
            'name' => $name,
            'description' => fake()->text(),
        ];
    }

    public function configure(): static
    {
        // mass assignment constraint workaround for tests
        return $this->afterMaking(function (Recipe $recipe) {
            $recipe->slug = $recipe->generateSlug();
        });
    }
}
