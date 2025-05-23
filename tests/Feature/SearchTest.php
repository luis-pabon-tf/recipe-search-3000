<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Author;
use App\Models\Recipe;
use App\Models\Ingredient;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function testHappyPath(): void
    {
        $author = Author::factory()->create([
            'email' => 'lolo_fuzzletoes@fakemail.com'
        ]);

        Recipe::factory()
            ->count(50)
            ->hasSteps(3)
            ->for($author)
            ->hasAttached(
                Ingredient::factory()->count(3),
                ['quantity' => 2]
            )
            ->create();

        $response = $this->post('/api/search', [
            'email' => $author->email,
        ]);
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'recipe' => [
                    '*' => [
                        'name',
                        'age',
                        'location'
                    ]
                ]
            ]
        ]);
    }
}
