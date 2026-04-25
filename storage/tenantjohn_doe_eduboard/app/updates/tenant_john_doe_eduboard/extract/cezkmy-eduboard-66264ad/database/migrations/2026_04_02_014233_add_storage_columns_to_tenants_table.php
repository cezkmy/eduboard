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
            $table->decimal('storage_limit_gb', 8, 2)->default(5.00)->after('plan');
            $table->decimal('storage_used_gb', 8, 2)->default(0.00)->after('storage_limit_gb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['storage_limit_gb', 'storage_used_gb']);
        });
    }
};
