<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('registrations')) {
            return;
        }

        $this->repairPrimaryKey();
        $this->widenLegacyColumns();
    }

    protected function repairPrimaryKey(): void
    {
        if (! Schema::hasColumn('registrations', 'id')) {
            DB::statement('ALTER TABLE `registrations` ADD COLUMN `id` BIGINT UNSIGNED NULL FIRST');
        }

        $stats = DB::table('registrations')
            ->selectRaw('COUNT(*) AS total, COUNT(`id`) AS with_id, COUNT(DISTINCT `id`) AS distinct_ids')
            ->first();

        if ((int) $stats->total > 0 && (
            (int) $stats->with_id < (int) $stats->total ||
            (int) $stats->distinct_ids < (int) $stats->with_id
        )) {
            DB::statement('SET @registration_sequence = 0');
            DB::statement(
                'UPDATE `registrations`
                 SET `id` = (@registration_sequence := @registration_sequence + 1)
                 ORDER BY `created_at`, `psa_id`'
            );
        }

        $hasPrimaryKey = DB::select(
            'SHOW INDEX FROM `registrations` WHERE Key_name = ?',
            ['PRIMARY'],
        ) !== [];

        if (! $hasPrimaryKey) {
            DB::statement('ALTER TABLE `registrations` MODIFY `id` BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE `registrations` ADD PRIMARY KEY (`id`)');
        }

        DB::statement(
            'ALTER TABLE `registrations`
             MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'
        );
    }

    protected function widenLegacyColumns(): void
    {
        $columns = Schema::getColumnListing('registrations');

        $definitions = [
            'psa_id' => 'VARCHAR(100) NULL',
            'prc_number' => 'INT NULL',
            'last_name' => 'VARCHAR(255) NULL',
            'first_name' => 'VARCHAR(255) NULL',
            'middle_name' => 'VARCHAR(255) NULL',
            'hospital_name' => 'VARCHAR(255) NULL',
            'hospital_address' => 'VARCHAR(255) NULL',
            'email' => 'VARCHAR(255) NULL',
            'contact_number' => 'VARCHAR(20) NULL',
            'membership' => 'VARCHAR(50) NULL',
            'discount_id' => 'VARCHAR(255) NULL',
            'proof_payment' => 'VARCHAR(255) NULL',
            'status' => 'VARCHAR(50) NULL',
            'rejection_title' => 'VARCHAR(255) NULL',
            'rejection_reason' => 'TEXT NULL',
            'country' => 'VARCHAR(255) NULL',
        ];

        foreach ($definitions as $column => $definition) {
            if (! in_array($column, $columns, true)) {
                continue;
            }

            DB::statement("ALTER TABLE `registrations` MODIFY `{$column}` {$definition}");
        }
    }

    public function down(): void
    {
        // The migration repairs existing identifiers and preserves imported data.
        // Reverting it would make Filament unable to address those records again.
    }
};
