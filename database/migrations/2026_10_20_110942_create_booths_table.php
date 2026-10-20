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
        Schema::create('booths', function (Blueprint $table) {
            $table->id('booth_id');
            $table->string('booth_type')->nullable();
            $table->string('booth_name');
            $table->string('location')->nullable();
            $table->string('booth_code');
            $table->string('booth_generated_code')->unique();
            $table->string('booth_qr')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booths');
    }
};
