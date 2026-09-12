<?php

namespace App\Domains\Place\Models;

use Illuminate\Database\Eloquent\Model;

class PageViewLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['social_page_id', 'date', 'views'];

    public function page() { return $this->belongsTo(SocialPage::class, 'social_page_id'); }
}
