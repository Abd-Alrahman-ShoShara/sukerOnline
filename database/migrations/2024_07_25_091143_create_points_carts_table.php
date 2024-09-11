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
        Schema::create('points_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pointsOrders_id');
            $table->foreign('pointsOrders_id')->references('id')->on('points_orders')->onDelete('cascade');
            $table->unsignedBigInteger('pointsProduct_id');
            $table->foreign('pointsProduct_id')->references('id')->on('points_products')->onDelete('cascade');
            $table->bigInteger('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_carts');
    }
};
