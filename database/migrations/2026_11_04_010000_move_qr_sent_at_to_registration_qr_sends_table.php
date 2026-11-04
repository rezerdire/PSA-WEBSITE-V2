<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('registration_qr_sends')) {
            Schema::create('registration_qr_sends', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
                $table->timestamp('sent_at');
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('registrations', 'qr_sent_at')) {
            $now = now();

            DB::table('registrations')
                ->whereNotNull('qr_sent_at')
                ->orderBy('id')
                ->get(['id', 'qr_sent_at'])
                ->each(function (object $registration) use ($now): void {
                    DB::table('registration_qr_sends')->insert([
                        'registration_id' => $registration->id,
                        'sent_at' => $registration->qr_sent_at,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });

            Schema::table('registrations', function (Blueprint $table): void {
                $table->dropColumn('qr_sent_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('registrations') && ! Schema::hasColumn('registrations', 'qr_sent_at')) {
            Schema::table('registrations', function (Blueprint $table): void {
                $table->timestamp('qr_sent_at')->nullable();
            });
        }

        if (Schema::hasTable('registration_qr_sends') && Schema::hasTable('registrations')) {
            DB::table('registration_qr_sends')
                ->select('registration_id', DB::raw('MAX(sent_at) AS sent_at'))
                ->groupBy('registration_id')
                ->get()
                ->each(function (object $send): void {
                    DB::table('registrations')
                        ->where('id', $send->registration_id)
                        ->update(['qr_sent_at' => $send->sent_at]);
                });
        }

        Schema::dropIfExists('registration_qr_sends');
    }
};
