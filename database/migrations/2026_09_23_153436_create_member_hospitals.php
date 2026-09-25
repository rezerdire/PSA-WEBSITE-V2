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
        Schema::create('member_hospitals', function (Blueprint $table) {
               $table->id();
            $table->string('member_id_no', 4);
            $table->string('hospital', 149)->nullable();
            $table->string('hosp_address', 199)->nullable();
            $table->string('hosp_hours', 50)->nullable();
            $table->string('hosp_tel_no', 50)->nullable();
            $table->string('hosp_designation', 50)->nullable();
            $table->string('hosp_days', 50)->nullable();
            $table->string('hosp_remarks', 50)->nullable();
            $table->boolean('hosp_primary')->default(false);
            $table->timestamps();

            $table->foreign('member_id_no')
                ->references('member_id_no')
                ->on('members')
                ->cascadeOnDelete();

            $table->index('member_id_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_hospitals');
    }
};













