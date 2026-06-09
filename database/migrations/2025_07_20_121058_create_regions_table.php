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
        Schema::create('regions', function (Blueprint $table) {
            $table->string('code', 3)->primary(); // Ex: EN, NW, AD
            $table->string('nom');
            $table->string('chef_lieu');
            $table->integer('population')->nullable();
            $table->float('superficie')->nullable();
            $table->text('description')->nullable();

            // Clé étrangère vers pays
            $table->string('pays_code', 2)->nullable(); // Doit correspondre à la clé primaire dans `pays`
            $table->foreign('pays_code')
                  ->references('code')
                  ->on('pays')
                  ->onDelete('cascade'); // Optionnel selon ton besoin

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};