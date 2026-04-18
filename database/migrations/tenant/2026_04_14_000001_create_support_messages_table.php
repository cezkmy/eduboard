<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_user_id');
            $table->string('from_name');
            $table->string('from_role');         // 'admin', 'teacher', 'student'
            $table->unsignedBigInteger('to_user_id')->nullable(); // null = to school admin
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['to_user_id', 'is_read']);
            $table->index('from_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
