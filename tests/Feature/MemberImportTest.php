<?php

use App\Services\TableImportExportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::dropIfExists('members');

    Schema::create('members', function (Blueprint $table): void {
        $table->string('member_id_no')->primary();
        $table->string('psa_chapter_code')->nullable();
        $table->string('psa_mem_type')->nullable();
        $table->string('mem_stat')->nullable();
        $table->string('mem_last_name')->nullable();
        $table->string('mem_first_name')->nullable();
        $table->string('mem_middle_name')->nullable();
        $table->text('mem_home_address')->nullable();
        $table->string('mem_mobile_no1')->nullable();
        $table->string('mem_email_address')->nullable();
        $table->string('mem_gender')->nullable();
        $table->string('mem_prc_no')->nullable();
        $table->timestamps();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('members');
});

function memberImportCsv(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'member-import-');

    $csv = "\xEF\xBB\xBFmember_id_no,psa_chapter_code,psa_mem_type,mem_stat,mem_last_name,mem_first_name,mem_middle_name,mem_home_address,mem_mobile_no1,mem_email_address,mem_gender,mem_prc_no\n";

    foreach ($rows as $row) {
        $csv .= implode(',', $row) . "\n";
    }

    file_put_contents($path, $csv);

    return $path;
}

test('member CSV imports overwrite existing records and insert new members', function (): void {
    DB::table('members')->insert([
        'member_id_no' => '0001',
        'psa_chapter_code' => 'NCR',
        'psa_mem_type' => 'RM',
        'mem_stat' => 'Active',
        'mem_last_name' => 'OLD',
        'mem_first_name' => 'NAME',
        'mem_middle_name' => 'O.',
    ]);

    $path = memberImportCsv([
        ['0001', 'NCR', 'RM', 'Active', 'CORRECTED', 'NAME', 'C.', 'Address', '0917', 'corrected@example.test', 'Female', '123456'],
        ['0002', 'NCR', 'LM', 'Active', 'NEW', 'MEMBER', 'M.', 'Address 2', '0918', 'new@example.test', 'Male', '654321'],
    ]);

    try {
        $result = app(TableImportExportService::class)->importTable(
            $path,
            'members',
            overwriteExisting: true,
        );
    } finally {
        unlink($path);
    }

    expect($result)->toBe([
        'inserted' => 1,
        'updated' => 1,
        'skipped' => 0,
    ]);

    expect(DB::table('members')->where('member_id_no', '0001')->first())
        ->toMatchArray([
            'mem_last_name' => 'CORRECTED',
            'mem_first_name' => 'NAME',
            'mem_middle_name' => 'C.',
            'mem_email_address' => 'corrected@example.test',
        ]);

    expect(DB::table('members')->where('member_id_no', '0002')->exists())->toBeTrue();
});

test('existing rows remain skipped unless overwrite is enabled', function (): void {
    DB::table('members')->insert([
        'member_id_no' => '0001',
        'psa_chapter_code' => 'NCR',
        'psa_mem_type' => 'RM',
        'mem_stat' => 'Active',
        'mem_last_name' => 'OLD',
        'mem_first_name' => 'NAME',
    ]);

    $path = memberImportCsv([
        ['0001', 'NCR', 'RM', 'Active', 'CORRECTED', 'NAME', 'C.', '', '', '', '', ''],
    ]);

    try {
        $result = app(TableImportExportService::class)->importTable($path, 'members');
    } finally {
        unlink($path);
    }

    expect($result)->toBe([
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 1,
    ]);

    expect(DB::table('members')->where('member_id_no', '0001')->value('mem_last_name'))
        ->toBe('OLD');
});
