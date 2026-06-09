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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Titre de la vidéo
            $table->string('slug')->unique(); // Slug pour l'URL
            $table->unsignedBigInteger('category_id'); // Clé étrangère vers la table catégories
            $table->text('description')->nullable(); // Description de la vidéo
            $table->string('url'); // URL de la vidéo (YouTube, Vimeo, etc.)
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); // Statut de l'article
            $table->timestamps(); // created_at et updated_at

            // Définition de la clé étrangère
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
