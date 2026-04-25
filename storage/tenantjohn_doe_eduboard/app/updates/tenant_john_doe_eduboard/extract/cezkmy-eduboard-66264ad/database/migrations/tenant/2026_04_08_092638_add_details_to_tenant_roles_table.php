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
        Schema::table('tenant_roles', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_roles', 'display_name')) {
                $table->string('display_name')->after('name')->nullable();
            }
            if (!Schema::hasColumn('tenant_roles', 'description')) {
                $table->text('description')->after('display_name')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_roles', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'description']);
        });
    }
};
