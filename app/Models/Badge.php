<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = ['name', 'name_ne', 'color', 'icon', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];
}
