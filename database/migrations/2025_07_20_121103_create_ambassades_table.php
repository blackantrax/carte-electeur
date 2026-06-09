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
        Schema::create('ambassades', function (Blueprint $table) {
            $table->string('code', 15)->primary(); // Format: PAY-NN
            $table->string('nom');
            $table->string('type'); // Ambassade, Consulat, Haut-Commissariat
            $table->string('pays_code', 2); // Code ISO du pays
            $table->string('ville');
            $table->string('adresse');
            $table->string('telephone');
            $table->string('email')->nullable();
            $table->string('responsable')->nullable();
            $table->integer('nombre_electeurs')->default(0);
            $table->timestamps();

            $table->foreign('pays_code')
                  ->references('code')
                  ->on('pays')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ambassades');
    }
};
