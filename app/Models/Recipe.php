<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Recipe extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // generate slug during creation
         static::creating(function (Recipe $recipe) {
            if (empty($recipe->slug)) {
                $recipe->generateSlug();
            }
         });

     }

    public function generateSlug(): string
    {
        // append a number when the slug name is very similar
        $slug = Str::slug($this->name);
        $matchCount = Recipe::where('slug', 'like', $slug . '%')->count();

        if ($matchCount > 0) {
            $slug .= '-' . $matchCount;
        }
        return $slug;
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredient')
            ->withPivot('quantity');
    }
}
