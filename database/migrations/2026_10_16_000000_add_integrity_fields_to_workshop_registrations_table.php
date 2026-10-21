<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshop_registrations', function (Blueprint $table): void {
            $table->timestamp('privacy_consented_at')->nullable()->after('workshops');
            $table->unique('psa_id', 'workshop_registrations_psa_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('workshop_registrations', function (Blueprint $table): void {
            $table->dropUnique('workshop_registrations_psa_id_unique');
            $table->dropColumn('privacy_consented_at');
        });
    }
};
