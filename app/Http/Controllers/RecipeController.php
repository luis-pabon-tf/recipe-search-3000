<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecipeResource;
use App\Models\Recipe;

class RecipeController extends Controller
{
   public function show(String $slug)
   {
        return new RecipeResource(Recipe::whereSlug($slug)->first());
   }
}
