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
        Schema::create('member_pictures', function (Blueprint $table) {
            $table->id();
            $table->string('psa_id');
            $table->string('mem_pic')->nullable(); // path to current/latest photo
            $table->timestamps();

            $table->foreign('psa_id')
                ->references('member_id_no')
                ->on('members')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unique('psa_id'); // one row per member (current photo only)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_pictures');
    }
};
