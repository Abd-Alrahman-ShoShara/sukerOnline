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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('password');
            $table->string('fcm_token')->nullable();
            $table->string('role')->default('1');
            $table->enum('language',['en','ar'])->default('ar');
            $table->string('verification_code')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('nameOfStore')->nullable();
            $table->string('adress')->nullable();
            $table->bigInteger('userPoints')->default(0);
            $table->unsignedBigInteger('classification_id')->nullable();
            $table->softDeletes();
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('cascade');            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
