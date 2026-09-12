<?php

namespace App\Domains\Place\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PageActivityLog extends Model
{
    protected $fillable = ['social_page_id', 'user_id', 'action', 'description', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function page()
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(int $pageId, string $action, string $description, array $meta = []): void
    {
        static::create([
            'social_page_id' => $pageId,
            'user_id'        => auth()->id(),
            'action'         => $action,
            'description'    => $description,
            'meta'           => $meta ?: null,
        ]);
    }
}
