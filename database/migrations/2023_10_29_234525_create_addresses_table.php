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
        Schema::create('addresses', function (Blueprint $table) {
            $table->comment('Adresses information.');
            $table->id();
            $table->string('road_type',30);
            $table->string('road_name', 150);
            $table->string('road_number', 5);
            $table->string('road_letter', 5)->nullable();
            $table->string('road_km', 10)->nullable();
            $table->string('block', 10)->nullable();
            $table->string('portal', 10)->nullable();
            $table->string('stair', 10)->nullable();
            $table->string('floor', 5)->nullable();
            $table->string('door', 5)->nullable();
            $table->string('town_entity', 50)->nullable();
            $table->string('town_name', 50);
            $table->string('province', 50);
            $table->string('country', 50);
            $table->string('postal_code', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adresses');
    }
};
