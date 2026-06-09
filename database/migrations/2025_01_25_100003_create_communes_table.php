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
        Schema::create('communes', function (Blueprint $table) {
            $table->string('code', 8)->primary(); // Format: DEP-NNN (ex: LT-01-01)
            $table->string('nom');
            $table->string('type'); // Urbaine, Rurale
            $table->string('departement_code', 5);
            $table->string('maire')->nullable();
            $table->integer('population')->nullable();
            $table->timestamps();

            $table->foreign('departement_code')
                  ->references('code')
                  ->on('departements')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
