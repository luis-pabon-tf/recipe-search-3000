<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
   public function show(String $slug)
   {
        return Recipe::whereSlug($slug)->first()->toArray();
   }
}
