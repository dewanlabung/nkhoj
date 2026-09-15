<?php

namespace App\Domains\Place\Models;

use Illuminate\Database\Eloquent\Model;

class PageProduct extends Model
{
    protected $fillable = [
        'social_page_id', 'name', 'description', 'price', 'currency',
        'image_url', 'link_url', 'is_available', 'sort_order',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }
}
