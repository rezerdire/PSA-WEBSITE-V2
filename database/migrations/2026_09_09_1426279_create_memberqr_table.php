<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members_qr', function (Blueprint $table) {
            $table->id();
            $table->string('member_id_no');
            $table->string('qr_path');
            $table->string('qr_hash', 64);
            $table->timestamps();

            $table->unique('member_id_no');

            $table->foreign('member_id_no')
                ->references('member_id_no')->on('members')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members_qr');
    }
};