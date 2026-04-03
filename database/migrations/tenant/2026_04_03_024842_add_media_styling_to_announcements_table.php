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
            if (!Schema::hasColumn('announcements', 'border_radius')) {
                $table->integer('border_radius')->default(24)->after('layout_type');
            }
            if (!Schema::hasColumn('announcements', 'media_layout')) {
                $table->string('media_layout')->default('landscape')->after('border_radius');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['border_radius', 'media_layout']);
        });
    }
};
