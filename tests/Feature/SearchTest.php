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

    private ?Author $author;
    private ?Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->author = Author::factory()->create([
            'email' => 'lolo_fuzzletoes@fakemail.com'
        ]);

        $this->recipe = Recipe::factory()
            ->hasSteps(1, [
                'description' => 'search by keyword',
            ])
            ->for($this->author)
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old tomato']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'fried needle',
                'description' => 'first of two',
            ]);

    }

    public function testVerifyStructure(): void
    {
        $response = $this->post('/api/search', [
            'email' => $this->author->email,
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
        $this->assertCount(1, $r_data['ingredients']);
        $ing_data = $r_data['ingredients'][0];
        $this->assertArrayHasKey('name', $ing_data);
        $this->assertArrayHasKey('unit_type', $ing_data);
        $this->assertArrayHasKey('quantity', $ing_data);

        $this->assertArrayHasKey('steps', $r_data);
        $this->assertCount(1, $r_data['steps']);
        $step_data = $r_data['steps'][0];
        $this->assertArrayHasKey('description', $step_data);
        $this->assertArrayHasKey('step_number', $step_data);
    }

    public function testEmailSearch(): void
    {
        Recipe::factory()
            ->count(5)
            ->hasSteps(3)
            ->for($this->author)
            ->hasAttached(
                Ingredient::factory()->count(2),
                ['quantity' => 2]
            )
            ->create();

        $small_author = Author::factory()->create([
            'email' => 'plumes_frankin@fakemail.com'
        ]);
        Recipe::factory()
            ->hasSteps(1)
            ->for($small_author)
            ->hasAttached(
                Ingredient::factory(),
                ['quantity' => 2]
            )
            ->create();

        // check big author matches
        $response = $this->post('/api/search', [
            'email' => $this->author->email,
        ]);
        $json = $response->json();
        $this->assertCount(6, $json['data']);
        $this->assertEquals($this->author->email, $json['data'][0]['author_email']);

        // check only small author matches
        $response = $this->post('/api/search', [
            'email' => $small_author->email,
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals($small_author->email, $json['data'][0]['author_email']);
    }

    public function testKeywordSearch(): void
    {
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
        $this->assertEquals('search by keyword', $json['data'][0]['steps'][0]['description']);

        // one match on ingredient
        $response = $this->post('/api/search', [
            'keyword' => 'old tomato',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals('old tomato', $json['data'][0]['ingredients'][0]['name']);

        // one match on description
        $response = $this->post('/api/search', [
            'keyword' => 'first of two',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals('first of two', $json['data'][0]['description']);

        // TWO matches on name
        $response = $this->post('/api/search', [
            'keyword' => 'fried needle',
        ]);
        $json = $response->json();
        $this->assertCount(2, $json['data']);
        $this->assertEquals('fried needle', $json['data'][0]['name']);
        $this->assertEquals('fried needle', $json['data'][1]['name']);
    }

    public function testIngredientSearch(): void
    {
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
        // same author
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'stepuno',
            ])
            ->for($this->author)
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'onion']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'boiled berries',
                'description' => 'first of two',
            ]);

        // same keywords
        $author2 = Author::factory()->create([
            'email' => 'plumes_frankin@fakemail.com'
        ]);
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'stepuno',
            ])
            ->for($author2)
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old tomato']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'fried needle',
                'description' => 'first of two',
            ]);

        // email matches r1 + r2, keyword matches r1
        $response = $this->post('/api/search', [
            'email' => $this->author->email,
            'keyword' => 'boiled berries',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals($this->author->email, $json['data'][0]['author_email']);
        $this->assertEquals('boiled berries', $json['data'][0]['name']);

        // email matches r1 + r2, keyword matches r1 + r2 + r3
        $response = $this->post('/api/search', [
            'email' => $this->author->email,
            'keyword' => 'first of two',
        ]);
        $json = $response->json();
        $this->assertCount(2, $json['data']);
        $this->assertEquals($this->author->email, $json['data'][0]['author_email']);
        $this->assertEquals('first of two', $json['data'][0]['description']);

        // email matches r3, keyword matches r1 + r2 + r3
        $response = $this->post('/api/search', [
            'email' => $author2->email,
            'keyword' => 'first of two',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals($author2->email, $json['data'][0]['author_email']);
        $this->assertEquals('first of two', $json['data'][0]['description']);
    }

    public function testKeywordAndIngredientSearch(): void
    {
        // same keywords
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'stepuno',
            ])
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old tomato']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'fried needle',
                'description' => 'first of two',
            ]);

        // same ingredients
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'somethingelse',
            ])
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old tomato']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'somethingelse',
                'description' => 'somethingelse',
            ]);

        // keyword matches r1 + r2, ingredient matches r1 + r2 + r3
        $response = $this->post('/api/search', [
            'keyword' => 'fried needle',
            'ingredient' => 'old',
        ]);
        $json = $response->json();
        $this->assertCount(2, $json['data']);
        $this->assertEquals('fried needle', $json['data'][0]['name']);
        $this->assertStringContainsString('old', $json['data'][0]['ingredients'][0]['name']);
    }

    public function testAuthorAndIngredientSearch(): void
    {
        // same author
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'stepuno',
            ])
            ->for($this->author)
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'somethingelse']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'somethingelse',
                'description' => 'somethingelse',
            ]);

        // same ingredients
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'somethingelse',
            ])
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old tomato']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'somethingelse',
                'description' => 'somethingelse',
            ]);

        // author matches r1 + r2, ingredient matches r1 + r2 + r3
        $response = $this->post('/api/search', [
            'email' => $this->author->email,
            'ingredient' => 'old',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals($this->author->email, $json['data'][0]['author_email']);
        $this->assertStringContainsString('old', $json['data'][0]['ingredients'][0]['name']);
    }

    public function testAllFieldsSearch(): void
    {
        // same author
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'somethingelse',
            ])
            ->for($this->author)
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'somethingelse']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'somethingelse',
                'description' => 'somethingelse',
            ]);

        // same keywords
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'stepuno',
            ])
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old tomato']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'fried needle',
                'description' => 'first of two',
            ]);

        // same ingredients
        Recipe::factory()
            ->hasSteps(1, [
                'description' => 'somethingelse',
            ])
            ->for(Author::factory()->create())
            ->hasAttached(
                Ingredient::factory()->create(['name' => 'old tomato']),
                ['quantity' => 1]
            )
            ->create([
                'name' => 'somethingelse',
                'description' => 'somethingelse',
            ]);


        // author matches r1 + r2, keyword matches r1 + r2, ingredient matches r1 + r2 + r3
        $response = $this->post('/api/search', [
            'email' => $this->author->email,
            'keyword' => 'fried needle',
            'ingredient' => 'old',
        ]);
        $json = $response->json();
        $this->assertCount(1, $json['data']);
        $this->assertEquals($this->author->email, $json['data'][0]['author_email']);
        $this->assertStringContainsString('old', $json['data'][0]['ingredients'][0]['name']);
    }

    public function testEmptySearch(): void
    {
        $response = $this->post('/api/search', []);
        $response->assertBadRequest();

        $response = $this->post('/api/search', [
            'email' => '',
        ]);
        $response->assertBadRequest();

        $response = $this->post('/api/search', [
            'email' => '',
            'keyword' => 'fried needle',
        ]);
        $response->assertAccepted();
    }

    public function testNoBodyProblem(): void
    {
        $response = $this->post('/api/search');
        $response->assertFound();
    }
}
