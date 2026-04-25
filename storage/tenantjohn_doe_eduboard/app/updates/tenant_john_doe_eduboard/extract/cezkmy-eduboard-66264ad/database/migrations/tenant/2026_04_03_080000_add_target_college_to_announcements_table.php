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
            $table->string('target_college')->nullable()->after('is_pinned');
            $table->string('target_year')->nullable()->change(); // Ensure it's string
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('target_college');
            // Can't easily change back to integer without potential data loss, 
            // but we'll leave it as string if needed.
        });
    }
};
