<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['parent_id', 'slug', 'name_en', 'name_ne', 'meta_title', 'sort_order', 'is_active', 'is_exclusive', 'color', 'icon'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_exclusive' => 'boolean'];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
