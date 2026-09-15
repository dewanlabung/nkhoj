<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name', 'short_form', 'code', 'editor_language', 'direction', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];
}
