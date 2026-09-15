<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('icon', 10)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed from existing hardcoded list
        $categories = [
            'Business & Commerce', 'Food & Restaurants', 'Arts & Entertainment',
            'Health & Wellness', 'Beauty & Personal Care', 'Fashion & Apparel',
            'Travel & Tourism', 'Education & Training', 'Technology & Software',
            'News & Media', 'Sports & Recreation', 'Music & Bands',
            'Non-profit & Causes', 'Community & Government', 'Real Estate',
            'Automotive', 'Home & Garden', 'Pets & Animals',
            'Finance & Insurance', 'Legal & Professional', 'Religious Organization', 'Other',
        ];

        $icons = [
            'Business & Commerce' => '🏢', 'Food & Restaurants' => '🍽️',
            'Arts & Entertainment' => '🎨', 'Health & Wellness' => '💊',
            'Beauty & Personal Care' => '💄', 'Fashion & Apparel' => '👗',
            'Travel & Tourism' => '✈️', 'Education & Training' => '📚',
            'Technology & Software' => '💻', 'News & Media' => '📰',
            'Sports & Recreation' => '⚽', 'Music & Bands' => '🎵',
            'Non-profit & Causes' => '🤝', 'Community & Government' => '🏛️',
            'Real Estate' => '🏠', 'Automotive' => '🚗',
            'Home & Garden' => '🌿', 'Pets & Animals' => '🐾',
            'Finance & Insurance' => '💰', 'Legal & Professional' => '⚖️',
            'Religious Organization' => '⛪', 'Other' => '📌',
        ];

        foreach ($categories as $i => $name) {
            DB::table('page_categories')->insert([
                'name'       => $name,
                'slug'       => Str::slug($name),
                'icon'       => $icons[$name] ?? null,
                'sort_order' => $i,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('page_categories');
    }
};
