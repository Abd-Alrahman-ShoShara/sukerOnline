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
        Schema::create('classification_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classification_id');
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classification_products');
    }
};
