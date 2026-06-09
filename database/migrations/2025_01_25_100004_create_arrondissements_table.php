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
        Schema::create('arrondissements', function (Blueprint $table) {
            $table->string('code', 15)->primary(); // Format: COM-NN (ex: LT-01-01-01)
            $table->string('nom');
            $table->string('commune_code', 8);
            $table->string('chef_lieu')->nullable();
            $table->timestamps();

            $table->foreign('commune_code')
                  ->references('code')
                  ->on('communes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arrondissements');
    }
};
