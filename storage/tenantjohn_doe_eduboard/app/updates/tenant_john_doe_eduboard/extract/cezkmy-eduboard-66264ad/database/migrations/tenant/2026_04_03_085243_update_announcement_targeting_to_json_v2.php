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
             $table->json('target_college')->nullable()->change();
             $table->json('target_program')->nullable()->change();
             $table->json('target_year')->nullable()->change();
             $table->json('target_grade_level')->nullable()->change();
             $table->json('target_strand')->nullable()->change();
             $table->json('target_section')->nullable()->change();
         });
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::table('announcements', function (Blueprint $table) {
             $table->string('target_college')->nullable()->change();
             $table->string('target_program')->nullable()->change();
             $table->string('target_year')->nullable()->change();
             $table->string('target_grade_level')->nullable()->change();
             $table->string('target_strand')->nullable()->change();
             $table->string('target_section')->nullable()->change();
         });
     }
 };
