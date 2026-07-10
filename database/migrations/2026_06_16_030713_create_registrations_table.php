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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('psa_id', 100);
            $table->integer('prc_number');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('hospital_name');
            $table->string('hospital_address');
            $table->string('email');
            $table->string('contact_number');
            $table->string('membership');
            $table->string('discount_id')->nullable();
            $table->string('proof_payment')->nullable();
            $table->string('status');
            $table->string('country');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
