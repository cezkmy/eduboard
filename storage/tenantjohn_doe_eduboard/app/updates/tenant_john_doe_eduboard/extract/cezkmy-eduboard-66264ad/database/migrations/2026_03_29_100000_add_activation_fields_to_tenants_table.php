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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('plan');
            }
            if (!Schema::hasColumn('tenants', 'custom_disabled_message')) {
                $table->text('custom_disabled_message')->nullable()->after('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'custom_disabled_message']);
        });
    }
};
