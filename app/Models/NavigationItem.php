<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationItem extends Model
{
    protected $fillable = [
        'label', 'url', 'type', 'target_id', 'language',
        'sort_order', 'is_active', 'parent_id', 'open_in',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function children(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavigationItem::class, 'parent_id');
    }

    public function resolvedUrl(): string
    {
        if ($this->type === 'home') return '/';
        if ($this->type === 'custom' || $this->url) {
            return $this->url ?? '#';
        }
        if ($this->type === 'category' && $this->target_id) {
            $cat = \App\Models\Category::find($this->target_id);
            return $cat ? "/category/{$cat->slug}" : '#';
        }
        if ($this->type === 'page' && $this->target_id) {
            $page = \App\Models\Page::find($this->target_id);
            return $page ? "/pages/{$page->slug}" : '#';
        }
        if ($this->type === 'tag' && $this->target_id) {
            $tag = \App\Models\Tag::find($this->target_id);
            return $tag ? "/tag/{$tag->slug}" : '#';
        }
        return '#';
    }
}
