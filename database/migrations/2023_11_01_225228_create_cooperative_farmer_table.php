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
        Schema::create('cooperative_farmer', function (Blueprint $table) {
            $table->comment('Intermediate table from cooperative and farmer relationship');
            $table->id();
            $table->unsignedBigInteger('farmer_id');
            $table->unsignedBigInteger('cooperative_id');
            $table->boolean('partner');
            $table->boolean('active');
            $table->timestamps();

            $table->foreign('farmer_id')->references('id')->on('farmers');
            $table->foreign('cooperative_id')->references('id')->on('cooperatives');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooperative_farmer');
    }
};