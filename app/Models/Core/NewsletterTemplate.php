<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class NewsletterTemplate extends Model
{
    protected $fillable = ['name', 'subject', 'html_content', 'text_content', 'is_default'];
}
