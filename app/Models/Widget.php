<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $fillable = ['type', 'title', 'where_to_display', 'display_order', 'is_active', 'config'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config'    => 'array',
        ];
    }

    public static function types(): array
    {
        return [
            'popular_posts'      => 'Popular Posts',
            'popular_tags'       => 'Popular Tags',
            'recommended_posts'  => 'Recommended Posts',
            'voting_poll'        => 'Voting Poll',
            'follow_us'          => 'Follow Us',
            'newsletter'         => 'Newsletter Signup',
            'about_us'           => 'About Us',
            'category_grid'      => 'Category Grid (Icon Tiles)',
            'social_proof'       => 'Social Proof (Reader Stats)',
        ];
    }

    public static function positions(): array
    {
        return [
            'sidebar'       => 'Sidebar (all pages)',
            'post_sidebar'  => 'Post Page Sidebar',
            'category_page' => 'Category Page Sidebar',
            'home_top'      => 'Homepage — Top',
            'home_bottom'   => 'Homepage — Bottom',
            'latest_posts'  => 'Latest Posts Section',
            'footer'        => 'Footer',
        ];
    }

    public static function forPosition(string $position): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('where_to_display', $position)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }
}
