<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('slideshow_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slideshow_id')->constrained()->onDelete('cascade');
            $table->string('path');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('slideshow_images');
    }
};
