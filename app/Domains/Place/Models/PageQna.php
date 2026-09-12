<?php

namespace App\Domains\Place\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PageQna extends Model
{
    protected $table = 'page_qna';

    protected $fillable = [
        'social_page_id', 'user_id', 'question', 'answer',
        'answered_by', 'is_featured', 'is_visible',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_visible'  => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public function asker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answerer()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
