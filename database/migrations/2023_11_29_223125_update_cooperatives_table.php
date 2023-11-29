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
        Schema::table('cooperatives', function (Blueprint $table) {
            $table->mediumText("cooperative_sign")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cooperatives', function (Blueprint $table) {
            $table->dropColumn('cooperative_sign');
        });
    }
};
