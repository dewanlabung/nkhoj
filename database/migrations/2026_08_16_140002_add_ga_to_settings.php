<?php
// This migration is intentionally empty — SEO / GA settings are stored in
// storage/app/site_settings.json, not the database. No schema changes needed.
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    public function up(): void {}
    public function down(): void {}
};
