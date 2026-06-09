<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('slideshows', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); // Statut de l'article
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('slideshows');
    }
};
