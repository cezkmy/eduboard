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
        Schema::create('central_support_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->string('creator_name');
            $table->string('creator_role');
            $table->string('subject');
            $table->string('status')->default('open');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('creator_id');
            $table->index('status');
        });

        Schema::create('central_support_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('from_user_id');
            $table->string('from_name');
            $table->string('from_role');
            $table->unsignedBigInteger('to_user_id')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('conversation_id');
            $table->index(['to_user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_support_messages');
        Schema::dropIfExists('central_support_conversations');
    }
};
