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
        Schema::create('weights', function (Blueprint $table) {
            $table->comment('Weights information.');
            $table->id();
            $table->string('type',10);
            $table->string('kilos',10);
            $table->string('sampling',5)->nullable();
            $table->string('container',10)->nullable();
            $table->string('purple_percentage',10)->nullable();
            $table->string('rehu_percentage',10)->nullable();
            $table->string('leaves_percentage',10)->nullable();
            $table->unsignedBigInteger('receipt_id');
            $table->timestamps();

            $table->foreign('receipt_id')->references('id')->on('receipts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weights');
    }
};
