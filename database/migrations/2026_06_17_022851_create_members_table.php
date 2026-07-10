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
 
    Schema::create('members', function (Blueprint $table) {
                $table->string('member_id_no')->primary();
                $table->string('psa_chapter_code');
                $table->string('psa_mem_type');
                $table->string('mem_last_name');
                $table->string('mem_first_name');
                $table->string('mem_middle_name')->nullable();
                $table->text('mem_home_address')->nullable();
                $table->string('mem_mobile_no1')->nullable();
                $table->string('mem_email_address')->nullable();
                $table->date('mem_birth_date')->nullable();
                $table->string('mem_gender')->nullable();
                $table->string('mem_prc_no')->nullable();
                $table->string('mem_pma_id_no')->nullable();
                $table->string('mem_fellow_no')->nullable();
                $table->string('mem_phic_no')->nullable();
                $table->timestamps();
            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
