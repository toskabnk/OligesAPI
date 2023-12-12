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
        Schema::create('cooperatives', function (Blueprint $table) {
            $table->comment('Cooperatives information.');
            $table->id();
            $table->string('nif', 10)->unique();
            $table->string('name', 150);
            $table->string('phone_number', 15)->unique()->nullable();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('address_id')->unique();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('address_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooperatives');
    }
};
