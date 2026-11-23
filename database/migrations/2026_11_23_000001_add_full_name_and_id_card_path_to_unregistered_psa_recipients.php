<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unregistered_psa_recipients', function (Blueprint $table): void {
            $table->string('full_name')->nullable()->after('email');
            $table->string('id_card_path')->nullable()->after('full_name');
        });
    }

    public function down(): void
    {
        Schema::table('unregistered_psa_recipients', function (Blueprint $table): void {
            $table->dropColumn(['full_name', 'id_card_path']);
        });
    }
};
