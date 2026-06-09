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
        Schema::create('pays', function (Blueprint $table) {
            $table->string('code', 2)->primary(); // Code ISO
            $table->string('nom');
            $table->string('nom_anglais')->nullable();
            $table->string('continent');
            $table->string('indicatif_telephonique', 5);
            $table->string('devise')->nullable();
            $table->string('drapeau')->nullable(); // URL ou chemin du drapeau
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pays');
    }
};
