<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            }
            if (!Schema::hasColumn('announcements', 'media_paths')) {
                $table->json('media_paths')->nullable()->after('media');
            }
            if (!Schema::hasColumn('announcements', 'heart_count')) {
                $table->integer('heart_count')->default(0);
            }
            if (!Schema::hasColumn('announcements', 'like_count')) {
                $table->integer('like_count')->default(0);
            }
            if (!Schema::hasColumn('announcements', 'fire_count')) {
                $table->integer('fire_count')->default(0);
            }
            if (!Schema::hasColumn('announcements', 'sad_count')) {
                $table->integer('sad_count')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['pinned_at', 'media_paths', 'heart_count', 'like_count', 'fire_count', 'sad_count']);
        });
    }
};
