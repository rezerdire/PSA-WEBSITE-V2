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
        Schema::create('gallery_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_event_id')->constrained()->cascadeOnDelete();
            $table->string('label');       
            $table->string('slug');        
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->unique(['gallery_event_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_days');
    }
};
