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
        Schema::create('receipts', function (Blueprint $table) {
            $table->comment('Receipts information.');
            $table->id();
            $table->timestamp('date');
            $table->mediumText('sign');
            $table->string('albaran_number',10);
            $table->unsignedBigInteger('cooperative_id');
            $table->unsignedBigInteger('farmer_id');
            $table->timestamps();

            $table->foreign('cooperative_id')->references('id')->on('cooperatives');
            $table->foreign('farmer_id')->references('id')->on('farmers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
