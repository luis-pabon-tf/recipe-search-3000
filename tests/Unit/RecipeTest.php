<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Recipe;
use App\Models\Author;
use App\Models\Ingredient;

class RecipeTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testSlugUniqueness(): void
    {
        $recipe1 = Recipe::factory()
            ->hasSteps(1)
            ->for(Author::factory()->create())
            ->hasAttached(Ingredient::factory()->create(), ['quantity' => 1])
            ->create([
                'name' => 'fried needle'
            ]);

        $recipe2 = Recipe::factory()
            ->hasSteps(1)
            ->for(Author::factory()->create())
            ->hasAttached(Ingredient::factory()->create(), ['quantity' => 1])
            ->create([
                'name' => 'fried needle'
            ]);

        $this->assertStringContainsString('fried-needle', $recipe1->slug);
        $this->assertStringContainsString('fried-needle', $recipe2->slug);
        $this->assertNotEquals($recipe1->slug, $recipe2->slug);
    }
}
