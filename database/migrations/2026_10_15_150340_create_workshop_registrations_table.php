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
        Schema::create('workshop_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('psa_id')->nullable();
            $table->string('mem_last_name');
            $table->string('mem_first_name');
            $table->string('mem_middle_name');
            $table->string('mem_type')->nullable();
            $table->string('prc_number')->unique();
            $table->string('hospital');
            $table->string('email');
            $table->string('phone', 20);
            $table->json('workshops');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_registrations');
    }
};