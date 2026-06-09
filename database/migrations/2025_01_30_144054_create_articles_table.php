<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Exécute la migration.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('category_id'); // Clé étrangère vers la table catégories
            $table->text('content');
            $table->string('featured_image')->nullable(); // Image optionnelle
            $table->boolean('main')->default(false); // Indique si l'article est principal
            $table->boolean('secondary')->default(false); // Indique si l'article est secondaire
            $table->boolean('featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); // Statut de l'article
            $table->timestamps();

            // Définition de la clé étrangère
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Annule la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
