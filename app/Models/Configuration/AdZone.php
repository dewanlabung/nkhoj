<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;

class AdZone extends Model
{
    protected $fillable = ['name', 'position', 'code', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
