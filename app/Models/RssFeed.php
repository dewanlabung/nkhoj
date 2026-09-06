<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RssFeed extends Model
{
    protected $fillable = [
        'name', 'url', 'language', 'category_id', 'post_count',
        'auto_update', 'show_read_more', 'add_as_draft',
        'generate_keywords', 'read_more_text', 'default_image',
        'images_source', 'imported_count',
    ];

    protected $casts = [
        'auto_update'        => 'boolean',
        'show_read_more'     => 'boolean',
        'add_as_draft'       => 'boolean',
        'generate_keywords'  => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
