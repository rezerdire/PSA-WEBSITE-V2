<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unregistered_psa_recipients', function (Blueprint $table): void {
            $table->id();
            $table->string('psa_id')->unique();
            $table->string('email');
            $table->string('last_name')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unregistered_psa_recipients');
    }
};
