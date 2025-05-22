<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
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
                $unit = 'gram';
                break;
            case 2:
                $unit = 'tsp';
                break;
            default:
                $unit = 'portion';
        }

        return [
            'name' => fake()->streetName(),
            'unit_type' => $unit,
        ];
    }
}
