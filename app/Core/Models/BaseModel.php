<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    protected $guarded = [];

    public function getDisplayName(): string
    {
        return (string) $this;
    }
}
