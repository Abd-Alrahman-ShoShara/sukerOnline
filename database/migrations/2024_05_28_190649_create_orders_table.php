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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("points")->default(0);
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references('id')->on("users")->onDelete('cascade');
            $table->enum('state',['pending','preparing','sent','received'])->default('pending');
            $table->enum('type',['urgent','regular','stored'])->default('regular');
            $table->double('totalPrice')->default(0);
            $table->boolean('ReadOrNot')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
