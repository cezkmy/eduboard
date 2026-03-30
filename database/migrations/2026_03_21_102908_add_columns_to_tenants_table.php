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
            if (!Schema::hasColumn('tenants', 'school_name')) {
                $table->string('school_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('tenants', 'status')) {
                $table->string('status')->default('Active')->after('school_name');
            }
            if (!Schema::hasColumn('tenants', 'plan')) {
                $table->string('plan')->default('Basic')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['school_name', 'status', 'plan']);
        });
    }
};
