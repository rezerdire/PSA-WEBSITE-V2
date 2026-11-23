<?php

use App\Models\UnregisteredPsaRecipient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unregistered_psa_recipients', function (Blueprint $table): void {
            $table->string('first_name')->nullable()->after('full_name');
            $table->string('middle_name')->nullable()->after('first_name');
        });

        DB::table('unregistered_psa_recipients')->whereNotNull('full_name')->orderBy('id')->chunkById(500, function ($recipients): void {
            foreach ($recipients as $recipient) {
                $name = UnregisteredPsaRecipient::splitName($recipient->full_name);
                DB::table('unregistered_psa_recipients')->where('id', $recipient->id)->update($name);
            }
        });
    }

    public function down(): void
    {
        Schema::table('unregistered_psa_recipients', function (Blueprint $table): void {
            $table->dropColumn(['first_name', 'middle_name']);
        });
    }
};
