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

    public function testVerifyStructure(): void
    {
        $author = Author::factory()->create();

        Recipe::factory()
            // ->count(50)
            ->hasSteps(3)
            ->for($author)
            ->hasAttached(
                Ingredient::factory()->count(2),
                ['quantity' => 2]
            )
            ->create();

        $response = $this->post('/api/search', [
            'email' => $author->email,
        ]);
        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue(isset($json['links']));
        $this->assertTrue(isset($json['meta']));

        $this->assertTrue(isset($json['data']));
        $this->assertCount(1, $json['data']);

        $r_data = $json['data'][0];
        $this->assertArrayHasKey('id', $r_data);
        $this->assertArrayHasKey('name', $r_data);
        $this->assertArrayHasKey('description', $r_data);
        $this->assertArrayHasKey('slug', $r_data);
        $this->assertArrayHasKey('author_email', $r_data);

        $this->assertArrayHasKey('ingredients', $r_data);
        $this->assertCount(2, $r_data['ingredients']);
        $ing_data = $r_data['ingredients'][0];
        $this->assertArrayHasKey('name', $ing_data);
        $this->assertArrayHasKey('unit_type', $ing_data);
        $this->assertArrayHasKey('quantity', $ing_data);

        $this->assertArrayHasKey('steps', $r_data);
        $this->assertCount(3, $r_data['steps']);
        $step_data = $r_data['steps'][0];
        $this->assertArrayHasKey('description', $step_data);
        $this->assertArrayHasKey('step_number', $step_data);
    }

    public function testEmailSearch(): void
    {
        $big_author = Author::factory()->create([
            'email' => 'lolo_fuzzletoes@fakemail.com'
        ]);
        Recipe::factory()
            ->count(5)
            ->hasSteps(3)
            ->for($big_author)
            ->hasAttached(
                Ingredient::factory()->count(2),
                ['quantity' => 2]
            )
            ->create();

        $small_author = Author::factory()->create([
            'email' => 'plumes_frankin@fakemail.com'
        ]);
        Recipe::factory()
            ->hasSteps(3)
            ->for($small_author)
            ->hasAttached(
                Ingredient::factory()->count(2),
                ['quantity' => 2]
            )
            ->create();

        // check big author matches
        $response = $this->post('/api/search', [
            'email' => $big_author->email,
        ]);
        $json = $response->json();
        $this->assertCount(5, $json['data']);
        $this->assertEquals($big_author->email, $json['data'][0]['author_email']);
        $this->assertEquals(Recipe::all()->first()->slug, $json['data'][0]['slug']);

        // check only small author matches
        $response = $this->post('/api/search', [
            'email' => $small_author->email,
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals($small_author->email, $json['data'][0]['author_email']);
        $this->assertEquals(Recipe::all()->last()->slug, $json['data'][0]['slug']);
    }

    public function testKeywordSearch(): void
    {
        // first match
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'search by keyword',
            ])
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'matcha']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'fried needle',
                'description' => 'first of two',
            ]);

        // filler
        Recipe::factory()
            ->count(5)
            ->hasSteps(3)
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory(),
                ['quantity' => 1]
            )
            ->create();

        // same name as first, everything else different
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'still searching',
            ])
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'tapioca']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'fried needle',
                'description' => 'second of two',
            ]);

        // one match on step
        $response = $this->post('/api/search', [
            'keyword' => 'search by keyword',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals(Recipe::all()->first()->steps->first()->description,
            $json['data'][0]['steps'][0]['description']);

        // one match on ingredient
        $response = $this->post('/api/search', [
            'keyword' => 'matcha',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals(Recipe::all()->first()->ingredients->first()->name,
            $json['data'][0]['ingredients'][0]['name']);

        // one match on description
        $response = $this->post('/api/search', [
            'keyword' => 'first of two',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals(Recipe::all()->first()->description, $json['data'][0]['description']);

        // TWO matches on name
        $response = $this->post('/api/search', [
            'keyword' => 'fried needle',
        ]);
        $json = $response->json();
        $this->assertCount(2, $json['data']);
        $this->assertEquals(Recipe::all()->first()->name, $json['data'][0]['name']);
        $this->assertEquals(Recipe::all()->last()->name, $json['data'][1]['name']);
    }

    public function testIngredientSearch(): void
    {
            // this could be a partial match; for example, “potato” should match “3 large potatoes” in the ingredients list

        // first match
        Recipe::factory()
            ->hasSteps(1)
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old tomato']),
                ['quantity' => 1]
            )
            ->create();

        // filler
        Recipe::factory()
            ->count(5)
            ->hasSteps(3)
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory(),
                ['quantity' => 1]
            )
            ->create();

        // same name as first, everything else different
        Recipe::factory()
            ->hasSteps(1)
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old wine']),
                ['quantity' => 1]
            )
            ->create();

        // two matches on old
        $response = $this->post('/api/search', [
            'ingredient' => 'old',
        ]);
        $json = $response->json();
        $this->assertCount(2, $json['data']);
        $this->assertStringContainsString('old', $json['data'][0]['ingredients'][0]['name']);
        $this->assertStringContainsString('old', $json['data'][1]['ingredients'][0]['name']);

        // one match on wine
        $response = $this->post('/api/search', [
            'ingredient' => 'wine',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertStringContainsString('wine', $json['data'][0]['ingredients'][0]['name']);
    }

    public function testAuthorAndKeywordSearch(): void
    {

    }

    public function testKeywordAndIngredientSearch(): void
    {

    }

    public function testAuthorAndIngredientSearch(): void
    {

    }

    public function testEmptySearch(): void
    {

    }
}



// Feature tests - how do you prove the search feature works for all search combinations
// email, keywords, ingredients, combinations of them, pagination, nothing