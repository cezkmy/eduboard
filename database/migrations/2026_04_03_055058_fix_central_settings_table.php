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
        Schema::table('central_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('central_settings', 'key')) {
                $table->string('key')->unique()->after('id');
            }
            if (!Schema::hasColumn('central_settings', 'value')) {
                $table->text('value')->nullable()->after('key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('central_settings', function (Blueprint $table) {
            $table->dropColumn(['key', 'value']);
        });
    }
};
