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
            if (!Schema::hasColumn('tenants', 'latest_backup_path')) {
                $table->string('latest_backup_path')->nullable()->after('previous_version');
            }
            if (!Schema::hasColumn('tenants', 'latest_db_backup_path')) {
                $table->string('latest_db_backup_path')->nullable()->after('latest_backup_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['latest_backup_path', 'latest_db_backup_path']);
        });
    }
};
