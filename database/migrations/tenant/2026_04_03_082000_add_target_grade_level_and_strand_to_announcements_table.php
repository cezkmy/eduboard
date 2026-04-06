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
            if (!Schema::hasColumn('announcements', 'target_grade_level')) {
                $table->string('target_grade_level')->nullable()->after('target_year');
            }
            if (!Schema::hasColumn('announcements', 'target_strand')) {
                $table->string('target_strand')->nullable()->after('target_grade_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('target_grade_level');
            $table->dropColumn('target_strand');
        });
    }
};
