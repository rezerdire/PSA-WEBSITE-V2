<?php

use App\Models\Registration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicatePsaIds = DB::table('registrations')
            ->select('psa_id')
            ->groupBy('psa_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $duplicateActivePrcNumbers = DB::table('registrations')
            ->select('prc_number')
            ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
            ->groupBy('prc_number')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicatePsaIds > 0 || $duplicateActivePrcNumbers > 0) {
            throw new RuntimeException(sprintf(
                'Cannot add registration integrity indexes: found %d duplicate PSA IDs and %d duplicate active PRC numbers. Resolve duplicates before retrying.',
                $duplicatePsaIds,
                $duplicateActivePrcNumbers,
            ));
        }

        Schema::table('registrations', function (Blueprint $table): void {
            $table->integer('active_prc_number')->nullable();
        });

        DB::table('registrations')
            ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
            ->update(['active_prc_number' => DB::raw('prc_number')]);

        Schema::table('registrations', function (Blueprint $table): void {
            $table->unique('psa_id', 'registrations_psa_id_unique');
            $table->unique('active_prc_number', 'registrations_active_prc_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropUnique('registrations_psa_id_unique');
            $table->dropUnique('registrations_active_prc_number_unique');
            $table->dropColumn('active_prc_number');
        });
    }
};
