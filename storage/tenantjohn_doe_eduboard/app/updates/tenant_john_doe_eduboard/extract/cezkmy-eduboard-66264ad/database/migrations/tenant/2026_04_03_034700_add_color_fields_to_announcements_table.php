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
            $table->string('title_color')->nullable()->default('#111827');
            $table->string('content_color')->nullable()->default('#4b5563');
            $table->string('category_color')->nullable()->default('#4b5563');
            $table->string('border_color')->nullable()->default('transparent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['title_color', 'content_color', 'category_color', 'border_color']);
        });
    }
};
