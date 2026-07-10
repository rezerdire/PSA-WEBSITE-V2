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
        Schema::create('gallery_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_day_id')->constrained()->cascadeOnDelete();
            $table->string('name');        // ASEAN NIGHT
            $table->string('slug');        // asean-night
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->unique(['gallery_day_id', 'slug']);       
            
            });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_categories');
    }
};
