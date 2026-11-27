<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

beforeEach(function () {
    Schema::create('master_lists', function ($table) {
        $table->id();
        $table->string('psa_id', 100)->nullable()->index();
        $table->string('record_type', 20);
        $table->string('first_name');
        $table->string('last_name');
        $table->string('middle_name')->nullable();
        $table->string('payment_ref_no')->nullable();
        $table->string('vip_name')->nullable();
        $table->text('vip_role')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('master_lists');
});

test('masterlist import combines member and nonmember CSVs and keeps the previous list on invalid input', function () {
    $membersCsv = tempnam(sys_get_temp_dir(), 'master-members-');
    $nonmembersCsv = tempnam(sys_get_temp_dir(), 'master-nonmembers-');

    file_put_contents($membersCsv, "\xEF\xBB\xBFmember_id_no,mem_last_name,mem_first_name,mem_middle_name,payment_ref_no\n0042,\"DOE, JR.\",JANE,,G260000001\n1,NULL,NULL,NULL,V260020057\n");
    file_put_contents($nonmembersCsv, "nm_id_no,nm_first_name,nm_last_name,nm_middle_name,payment_ref_no\nNM0001,JOHN,SMITH,NULL,NULL\n");

    DB::table('master_lists')->insert([
        'psa_id' => 'OLD-ROW',
        'record_type' => 'member',
        'first_name' => 'Old',
        'last_name' => 'Record',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    try {
        expect(Artisan::call('masterlist:import', [
            'members' => $membersCsv,
            'nonmembers' => $nonmembersCsv,
        ]))->toBe(0);

        expect(DB::table('master_lists')->count())->toBe(2)
            ->and(DB::table('master_lists')->where('psa_id', '0042')->value('record_type'))->toBe('member')
            ->and(DB::table('master_lists')->where('psa_id', '0042')->value('last_name'))->toBe('DOE, JR.')
            ->and(DB::table('master_lists')->where('psa_id', '1')->exists())->toBeFalse()
            ->and(DB::table('master_lists')->where('psa_id', 'NM0001')->value('record_type'))->toBe('nonmember')
            ->and(DB::table('master_lists')->where('psa_id', 'NM0001')->value('middle_name'))->toBeNull()
            ->and(DB::table('master_lists')->where('psa_id', 'OLD-ROW')->exists())->toBeFalse();

        file_put_contents($membersCsv, "not,the,expected,headers\n");

        expect(Artisan::call('masterlist:import', [
            'members' => $membersCsv,
            'nonmembers' => $nonmembersCsv,
        ]))->toBe(1)
            ->and(DB::table('master_lists')->count())->toBe(2);
    } finally {
        @unlink($membersCsv);
        @unlink($nonmembersCsv);
    }
});

test('VIP workbook matches existing entries, stores missing IDs and survives CSV refresh', function () {
    $xlsx = tempnam(sys_get_temp_dir(), 'vip-');
    $membersCsv = tempnam(sys_get_temp_dir(), 'members-');
    $nonmembersCsv = tempnam(sys_get_temp_dir(), 'nonmembers-');
    $writer = new Writer();
    $writer->openToFile($xlsx);
    $writer->getCurrentSheet()->setName('Summary');
    $writer->addRow(Row::fromValues(['0042', 'DOE, JANE', 'SPEAKER', 'RM', 'REF42']));
    $writer->addRow(Row::fromValues([null, 'SMITH, JOHN', 'MODERATOR', null, 'REF99']));
    $writer->addRow(Row::fromValues([null, 'GUEST, AMY', 'SPEAKER', null, null]));
    $writer->close();
    file_put_contents($membersCsv, "member_id_no,mem_last_name,mem_first_name,mem_middle_name,payment_ref_no\n0042,DOE,JANET,,REF42\n");
    file_put_contents($nonmembersCsv, "nm_id_no,nm_first_name,nm_last_name,nm_middle_name,payment_ref_no\nNM99,JOHN,SMITH,,REF99\n");
    DB::table('master_lists')->insert([
        ['psa_id' => '0042', 'record_type' => 'member', 'first_name' => 'JANE', 'last_name' => 'DOE', 'payment_ref_no' => 'REF42'],
        ['psa_id' => 'NM99', 'record_type' => 'nonmember', 'first_name' => 'JOHN', 'last_name' => 'SMITH', 'payment_ref_no' => 'REF99'],
    ]);

    try {
        expect(Artisan::call('masterlist:import-vip', ['file' => $xlsx]))->toBe(0)
            ->and(DB::table('master_lists')->count())->toBe(3)
            ->and(DB::table('master_lists')->where('psa_id', '0042')->value('vip_role'))->toBe('SPEAKER')
            ->and(DB::table('master_lists')->where('psa_id', 'NM99')->value('vip_role'))->toBe('MODERATOR')
            ->and(DB::table('master_lists')->where('vip_name', 'GUEST, AMY')->value('psa_id'))->toBeNull();

        expect(Artisan::call('masterlist:import-vip', ['file' => $xlsx]))->toBe(0)
            ->and(DB::table('master_lists')->count())->toBe(3);

        expect(Artisan::call('masterlist:import', ['members' => $membersCsv, 'nonmembers' => $nonmembersCsv]))->toBe(0)
            ->and(DB::table('master_lists')->count())->toBe(3)
            ->and(DB::table('master_lists')->where('psa_id', '0042')->value('first_name'))->toBe('JANET')
            ->and(DB::table('master_lists')->where('psa_id', '0042')->value('vip_role'))->toBe('SPEAKER')
            ->and(DB::table('master_lists')->where('vip_name', 'GUEST, AMY')->exists())->toBeTrue();
    } finally {
        @unlink($xlsx);
        @unlink($membersCsv);
        @unlink($nonmembersCsv);
    }
});
