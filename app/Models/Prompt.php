<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    protected $fillable = ['name', 'description', 'content', 'is_active', 'version'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public static function active(string $name): ?self
    {
        return static::where('name', $name)->where('is_active', true)->first();
    }
}
