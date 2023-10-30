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
        Schema::create('farmers', function (Blueprint $table) {
            $table->comment('Farmers information.');
            $table->id();
            $table->string('dni', 10)->unique();
            $table->string('name', 150);
            $table->string('surname', 150);
            $table->string('phone_number', 15);
            $table->string('movil_number', 15);
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('cooperative_id');
            $table->unsignedBigInteger('address_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('cooperative_id')->references('id')->on('cooperatives');
            $table->foreign('address_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
