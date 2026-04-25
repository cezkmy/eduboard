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
            if (!Schema::hasColumn('announcements', 'bg_color')) {
                $table->string('bg_color')->nullable()->after('template_id');
            }
            if (!Schema::hasColumn('announcements', 'layout_type')) {
                $table->string('layout_type')->default('landscape')->after('bg_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['bg_color', 'layout_type']);
        });
    }
};
