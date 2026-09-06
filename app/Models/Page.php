<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'status', 'page_type',
        'menu_position', 'language', 'meta_title', 'meta_description',
    ];

    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
        }
        return $slug;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
