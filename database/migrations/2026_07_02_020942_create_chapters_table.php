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
        Schema::create('chapters', function (Blueprint $table) {
        $table->string('psa_chapter_code', 3)->primary();
            $table->string('psa_chapter_desc', 50)->nullable();
            $table->string('psa_chapter_address', 100)->nullable();
            $table->string('psa_chapter_president', 50)->nullable();
            $table->string('psa_chapter_contact_no', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
