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
        Schema::create('farms', function (Blueprint $table) {
            $table->comment('Farms information.');
            $table->id();
            $table->string('name', 150);
            $table->string('polygon', 10);
            $table->string('plot', 10);
            $table->unsignedBigInteger('farmer_id');
            $table->unsignedBigInteger('address_id');
            $table->timestamps();

            $table->foreign('farmer_id')->references('id')->on('farmers');
            $table->foreign('address_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};
