<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecipeCollection;
use Illuminate\Http\Request;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required_without_all:keyword,ingredient',
        ]);



        // $validator = Validator::make($request->all(), [
        //     'ids' => 'required_without_all:rental_company_id,camper_id,take_over_station_id, returnable_station_id'
        //     ], [
        //     'required_without_all' => 'One of the following Season,Rental companies,... must be present.'
        //     ]);

        if (!$validated) {
            abort(400, 'A body with search parameters is required');
        }

        $email = $request->input('email');
        $keyword = $request->input('keyword');
        $ingredient = $request->input('ingredient');

        $query = Recipe::query();

        if (!empty($email)) {
            // Author email - exact match
            $query = $query->whereHas('author', function(Builder $query) use ($email)
            {
                return $query->where('email', $email);
            });
        }

        if (!empty($keyword)) {
            // Keyword - should match ANY of these fields: name, description, ingredients, or steps
            $query->where(function(Builder $query) use ($keyword) {
                $query->where('name', $keyword)
                    ->orWhere('description', $keyword)
                    ->orWhereHas('ingredients', function(Builder $query) use ($keyword)
                    {
                        return $query->where('name', $keyword);
                    })
                    ->orWhereHas('steps', function(Builder $query) use ($keyword)
                    {
                        return $query->where('description', $keyword);
                });
            })->get();
        }

        if (!empty($ingredient)) {
            // this could be a partial match; for example, “potato” should match “3 large potatoes” in the ingredients list
            $query = $query->whereHas('ingredients', function(Builder $query) use ($ingredient)
            {
                $ingredientSingular = Str::singular($ingredient);
                $ingredientPlural = Str::plural($ingredient);

                return $query->where('name', 'LIKE', "%$ingredientSingular%")
                    ->orWhere('name', 'LIKE', "%$ingredientPlural%");
            });
        }

        // if (!empty($email) && !empty($keyword))
        //     var_dump($query->dump());

        return new RecipeCollection($query->paginate(10));
    }
}
