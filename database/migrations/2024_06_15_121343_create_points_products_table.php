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
        Schema::create('points_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('price');
            $table->string('description');
            $table->string('images');
            $table->integer('quantity')->default(0);
            $table->boolean('displayOrNot')->default(false);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_products');
    }
};
