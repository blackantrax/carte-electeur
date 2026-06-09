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
        Schema::create('bureau_de_votes', function (Blueprint $table) {
            $table->string('code', 20)->primary(); // Format: ARR-NNNN (ex: LT-01-01-01-001)
            $table->string('nom');
            $table->string('adresse');
            $table->string('commune_code', 8);
            $table->string('arrondissement_code', 15)->nullable();
            $table->integer('nombre_electeurs')->default(0);
            $table->string('localisation')->nullable(); // GPS coordinates
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('commune_code')
                  ->references('code')
                  ->on('communes')
                  ->onDelete('cascade');

            $table->foreign('arrondissement_code')
                  ->references('code')
                  ->on('arrondissements')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bureau_de_votes');
    }
};
