<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(['email' => 'admin@nkhoj.com'], [
            'uuid'     => Str::uuid(),
            'name'     => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('changeme123'),
            'role'     => 'admin',
        ]);

        // Root categories (bilingual)
        $cats = [
            ['slug' => 'politics',      'name_en' => 'Politics',     'name_ne' => 'राजनीति'],
            ['slug' => 'technology',    'name_en' => 'Technology',   'name_ne' => 'प्रविधि'],
            ['slug' => 'sports',        'name_en' => 'Sports',       'name_ne' => 'खेलकुद'],
            ['slug' => 'health',        'name_en' => 'Health',       'name_ne' => 'स्वास्थ्य'],
            ['slug' => 'entertainment', 'name_en' => 'Entertainment','name_ne' => 'मनोरञ्जन'],
            ['slug' => 'business',      'name_en' => 'Business',     'name_ne' => 'व्यापार'],
        ];

        foreach ($cats as $i => $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], array_merge($cat, ['sort_order' => $i]));
        }

        // Common tags
        $tags = ['nepal', 'kathmandu', 'viral', 'trending', 'breaking-news', 'opinion', 'analysis'];
        foreach ($tags as $slug) {
            Tag::firstOrCreate(['slug' => $slug], ['name_en' => ucwords(str_replace('-', ' ', $slug))]);
        }

        // Run sub-seeders
        $this->call([
            PromptSeeder::class,
            SampleContentSeeder::class,
        ]);
    }
}
