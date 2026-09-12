<?php

namespace App\Domains\Recipe\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeRating extends Model
{
    protected $fillable = ['recipe_id', 'user_id', 'rating', 'review'];

    public function recipe() { return $this->belongsTo(Recipe::class); }
    public function user()   { return $this->belongsTo(User::class); }
}
