<?php

use App\Filament\Resources\MasterLists\Pages\CreateMasterList;
use App\Filament\Resources\MasterLists\Pages\EditMasterList;
use App\Filament\Resources\MasterLists\Pages\ListMasterLists;
use App\Models\MasterList;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('master_lists', function (Blueprint $table): void {
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

    config(['app.env' => 'local']);
    $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password']);
});

afterEach(function (): void {
    Schema::dropIfExists('master_lists');
    Schema::dropIfExists('users');
});

test('an admin can create edit search and delete an evaluation master list entry', function (): void {
    Livewire::actingAs($this->admin)->test(CreateMasterList::class)
        ->fillForm([
            'psa_id' => 'NM0042',
            'record_type' => 'nonmember',
            'first_name' => 'Jane',
            'middle_name' => 'M.',
            'last_name' => 'Doe',
            'payment_ref_no' => 'REF42',
        ])->call('create')->assertHasNoFormErrors();

    $record = MasterList::where('psa_id', 'NM0042')->firstOrFail();
    expect($record->record_type)->toBe('nonmember');

    Livewire::actingAs($this->admin)->test(ListMasterLists::class)
        ->searchTable('NM0042')->assertCanSeeTableRecords([$record]);

    Livewire::actingAs($this->admin)->test(EditMasterList::class, ['record' => $record->getRouteKey()])
        ->fillForm(['first_name' => 'Janet'])->call('save')->assertHasNoFormErrors();
    expect($record->fresh()->first_name)->toBe('Janet');

    Livewire::actingAs($this->admin)->test(EditMasterList::class, ['record' => $record->getRouteKey()])
        ->callAction('delete');
    expect(MasterList::find($record->getKey()))->toBeNull();
});
