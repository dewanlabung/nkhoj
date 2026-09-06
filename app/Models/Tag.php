<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['slug', 'name_en', 'name_ne'];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
