<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Questions: close support + edit tracking
        Schema::table('questions', function (Blueprint $table) {
            $table->string('closed_reason')->nullable()->after('status');
            $table->unsignedBigInteger('duplicate_of')->nullable()->after('closed_reason');
            $table->timestamp('edited_at')->nullable()->after('updated_at');
        });

        // Answers: edit tracking
        Schema::table('answers', function (Blueprint $table) {
            $table->timestamp('edited_at')->nullable()->after('updated_at');
        });

        // Users: reputation
        Schema::table('users', function (Blueprint $table) {
            $table->integer('reputation')->default(0)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['closed_reason', 'duplicate_of', 'edited_at']);
        });
        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn('edited_at');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('reputation');
        });
    }
};
