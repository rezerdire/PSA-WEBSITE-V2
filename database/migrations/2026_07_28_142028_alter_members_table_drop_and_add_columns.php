<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnsToDrop = array_values(array_filter([
            'mem_birth_date',
            'mem_pma_id_no',
            'mem_fellow_no',
            'mem_phic_no',
            'mem_pic',
        ], static fn (string $column): bool => Schema::hasColumn('members', $column)));

        if ($columnsToDrop !== []) {
            Schema::table('members', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }

        if (!Schema::hasColumn('members', 'mem_stat')) {
            Schema::table('members', function (Blueprint $table): void {
                $table->string('mem_stat')->nullable()->after('psa_mem_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->date('mem_birth_date')->nullable();
            $table->string('mem_pma_id_no')->nullable();
            $table->string('mem_fellow_no')->nullable();
            $table->string('mem_phic_no')->nullable();
            $table->string('mem_pic')->nullable();
            $table->dropColumn('mem_stat');
        });
    }
};