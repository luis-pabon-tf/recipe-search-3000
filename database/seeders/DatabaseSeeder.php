<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $author = Author::factory()->create();

        Recipe::factory()
            ->count(10)
            ->hasSteps(3)
            ->for($author)
            ->hasAttached(
                Ingredient::factory()->count(3),
                ['quantity' => rand(1,5)]
            )
            ->create();
    }
}
