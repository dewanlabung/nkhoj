<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name', 'name_ne', 'slug', 'badge_label', 'badge_color', 'badge_icon',
        'permissions', 'is_default', 'is_system', 'ai_credits', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_default'  => 'boolean',
            'is_system'   => 'boolean',
        ];
    }

    public static function allPermissions(): array
    {
        return [
            'add_post'        => 'Add Post',
            'edit_own_post'   => 'Edit Own Post',
            'ai_writer'       => 'AI Writer',
            'categories'      => 'Categories',
            'tags'            => 'Tags',
            'comments'        => 'Comments',
            'contact'         => 'Contact',
            'polls'           => 'Polls',
            'media'           => 'Media',
            'widgets'         => 'Widgets',
            'ad_spaces'       => 'Ad Spaces',
            'newsletter'      => 'Newsletter',
            'users'           => 'Users',
            'roles'           => 'Roles & Permissions',
            'settings'        => 'Settings',
            'content_settings'=> 'Content Settings',
            'email_settings'  => 'Email Settings',
            'security'        => 'Security',
            'seo_tools'       => 'SEO Tools',
            'cache_system'    => 'Cache System',
            'analytics'       => 'Analytics',
            'backup'          => 'Backup',
        ];
    }

    public static function badgeOptions(): array
    {
        return [
            ['value' => '',          'label' => '— None —',     'color' => 'gray',   'icon' => ''],
            ['value' => 'Super Admin','label' => 'Super Admin', 'color' => 'red',    'icon' => 'shield'],
            ['value' => 'Admin',      'label' => 'Admin',        'color' => 'rose',   'icon' => 'shield'],
            ['value' => 'Editor',     'label' => 'Editor',       'color' => 'purple', 'icon' => 'pencil'],
            ['value' => 'Author',     'label' => 'Author',       'color' => 'green',  'icon' => 'user'],
            ['value' => 'Moderator',  'label' => 'Moderator',    'color' => 'blue',   'icon' => 'cog'],
            ['value' => 'Reporter',   'label' => 'Reporter',     'color' => 'indigo', 'icon' => 'newspaper'],
            ['value' => 'Contributor','label' => 'Contributor',  'color' => 'teal',   'icon' => 'plus'],
            ['value' => 'Member',     'label' => 'Member',       'color' => 'gray',   'icon' => 'user'],
            ['value' => 'VIP',        'label' => 'VIP',          'color' => 'amber',  'icon' => 'star'],
        ];
    }

    public function hasPermission(string $key): bool
    {
        if ($this->slug === 'admin') return true;
        return in_array($key, $this->permissions ?? []);
    }

    public function users()
    {
        return User::where('role', $this->slug)->count();
    }
}
