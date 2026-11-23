<?php

use App\Filament\Pages\UnregisteredPsaRecipients;
use App\Mail\UnregisteredPsaId;
use App\Models\UnregisteredPsaRecipient;
use App\Models\User;
use App\Services\PsaIdCardLocator;
use App\Services\UnregisteredPsaRecipientService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Schema::dropIfExists('unregistered_psa_recipients');
    Schema::create('unregistered_psa_recipients', function (Blueprint $table): void {
        $table->id();
        $table->string('psa_id')->unique();
        $table->string('email');
        $table->string('last_name')->nullable();
        $table->string('full_name')->nullable();
        $table->string('first_name')->nullable();
        $table->string('middle_name')->nullable();
        $table->string('chapter')->nullable();
        $table->string('id_card_path')->nullable();
        $table->string('status')->default('pending');
        $table->timestamp('sent_at')->nullable();
        $table->text('error')->nullable();
        $table->timestamps();
    });
});

afterEach(function (): void {
    PsaIdCardLocator::flush();
    Schema::dropIfExists('unregistered_psa_recipients');
    Schema::dropIfExists('users');
});

test('CSV imports upsert by PSA ID while preserving send status for existing recipients', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'unregistered-psa-');
    file_put_contents($path, "\xEF\xBB\xBFPSA ID,Full Name,Email,Chapter\n00123,Smith Example,old@example.test,Test\n");
    try {
        $service = app(UnregisteredPsaRecipientService::class);
        expect($service->import($path))->toBe(1);
        $recipient = UnregisteredPsaRecipient::firstOrFail();
        $recipient->update(['status' => 'sent', 'sent_at' => now()]);
        file_put_contents($path, "PSA ID,Full Name,Email,Chapter\n00123,Edited Name,new@example.test,Test\n00456,Next Example,next@example.test,Test\n");
        expect($service->import($path))->toBe(2);
        expect(UnregisteredPsaRecipient::count())->toBe(2)
            ->and($recipient->fresh()->email)->toBe('new@example.test')
            ->and($recipient->fresh()->full_name)->toBe('Edited Name')
            ->and($recipient->fresh()->first_name)->toBe('Edited')
            ->and($recipient->fresh()->last_name)->toBe('Name')
            ->and($recipient->fresh()->status)->toBe('sent')
            ->and(UnregisteredPsaRecipient::where('psa_id', '00456')->exists())->toBeTrue();
    } finally {
        unlink($path);
    }
});

test('CSV imports separate names and chapter from the remaining members export', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'unregistered-psa-');
    file_put_contents($path, "PSA ID,First Name,Middle Name,Last Name,Email,Chapter\n00933,SUSAN PURIFICACION,D.,GOOCO,swaneeh@example.test,NATIONAL CAPITAL REGION\n");

    try {
        expect(app(UnregisteredPsaRecipientService::class)->import($path))->toBe(1)
            ->and(UnregisteredPsaRecipient::firstOrFail()->only(['psa_id', 'first_name', 'middle_name', 'last_name', 'email', 'chapter']))->toBe([
                'psa_id' => '00933',
                'first_name' => 'SUSAN PURIFICACION',
                'middle_name' => 'D.',
                'last_name' => 'GOOCO',
                'email' => 'swaneeh@example.test',
                'chapter' => 'NATIONAL CAPITAL REGION',
            ]);
    } finally {
        unlink($path);
    }
});

test('CSV download exports split names and chapter', function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('remember_token')->nullable();
        $table->timestamps();
    });
    config(['app.env' => 'local']);
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
    ]);
    UnregisteredPsaRecipient::create([
        'psa_id' => '00933',
        'first_name' => 'SUSAN PURIFICACION',
        'middle_name' => 'D.',
        'last_name' => 'GOOCO',
        'email' => 'swaneeh@example.test',
        'chapter' => 'NATIONAL CAPITAL REGION',
    ]);

    Livewire\Livewire::actingAs($user)
        ->test(UnregisteredPsaRecipients::class)
        ->assertSee('First name')
        ->assertSee('Middle name')
        ->assertSee('Last name')
        ->assertSee('Chapter')
        ->assertSee('SUSAN PURIFICACION')
        ->callAction('downloadCsv')
        ->assertFileDownloaded(
            content: "\"PSA ID\",\"First Name\",\"Middle Name\",\"Last Name\",Email,Chapter\n00933,\"SUSAN PURIFICACION\",D.,GOOCO,swaneeh@example.test,\"NATIONAL CAPITAL REGION\"\n",
            contentType: 'text/csv; charset=UTF-8',
        );
});

test('manually created recipients require and save their email address', function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('remember_token')->nullable();
        $table->timestamps();
    });
    config(['app.env' => 'local']);
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
    ]);

    Livewire\Livewire::actingAs($user)
        ->test(UnregisteredPsaRecipients::class)
        ->mountAction('createRecipient')
        ->set('mountedActions.0.data', [
            'psa_id' => '00934',
            'first_name' => 'Casey',
            'middle_name' => 'M.',
            'last_name' => 'Example',
            'email' => 'casey@example.test',
            'chapter' => 'Central Luzon',
        ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    $recipient = UnregisteredPsaRecipient::where('psa_id', '00934')->firstOrFail();

    expect($recipient->email)->toBe('casey@example.test')
        ->and($recipient->chapter)->toBe('Central Luzon')
        ->and($recipient->full_name)->toBe('Casey M. Example');
});

test('editing a recipient saves its chapter', function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('remember_token')->nullable();
        $table->timestamps();
    });
    config(['app.env' => 'local']);
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
    ]);
    $recipient = UnregisteredPsaRecipient::create([
        'psa_id' => '00934',
        'first_name' => 'Casey',
        'middle_name' => 'M.',
        'last_name' => 'Example',
        'email' => 'casey@example.test',
        'chapter' => 'Old Chapter',
    ]);

    Livewire\Livewire::actingAs($user)
        ->test(UnregisteredPsaRecipients::class)
        ->mountTableAction('edit', $recipient->getKey())
        ->set('mountedActions.0.data', [
            'psa_id' => '00934',
            'first_name' => 'Casey',
            'middle_name' => 'M.',
            'last_name' => 'Example',
            'email' => 'casey@example.test',
            'chapter' => 'Central Luzon',
        ])
        ->callMountedTableAction()
        ->assertHasNoFormErrors();

    expect($recipient->fresh()->chapter)->toBe('Central Luzon');
});

test('bulk send uses the registration email view and tracks sent recipients', function (): void {
    Mail::fake();
    $recipient = UnregisteredPsaRecipient::create(['psa_id' => '00123', 'email' => 'member@example.test', 'last_name' => 'Smith']);
    $result = app(UnregisteredPsaRecipientService::class)->sendPending(10);

    expect($result)->toBe(['sent' => 1, 'failed' => 0])
        ->and($recipient->fresh()->status)->toBe('sent')
        ->and($recipient->fresh()->sent_at)->not->toBeNull();

    Mail::assertSent(UnregisteredPsaId::class, fn (UnregisteredPsaId $mail) => $mail->psaId === '00123' && $mail->lastName === 'Smith' && $mail->content()->with['registration']->last_name === 'Smith');
});

test('full names derive the final surname while dropping common name suffixes', function (): void {
    expect(UnregisteredPsaRecipient::splitName('ROMEO L. SANTOS, JR.'))->toBe([
        'first_name' => 'ROMEO',
        'middle_name' => 'L.',
        'last_name' => 'Santos',
    ]);
});

test('a selected recipient can be sent individually without sending other rows', function (): void {
    Mail::fake();
    $selected = UnregisteredPsaRecipient::create(['psa_id' => '00123', 'email' => 'selected@example.test']);
    $other = UnregisteredPsaRecipient::create(['psa_id' => '00456', 'email' => 'other@example.test']);

    expect(app(UnregisteredPsaRecipientService::class)->sendOne($selected))->toBeTrue()
        ->and($selected->fresh()->status)->toBe('sent')
        ->and($other->fresh()->status)->toBe('pending');

    Mail::assertSentCount(1);
    Mail::assertSent(UnregisteredPsaId::class, fn (UnregisteredPsaId $mail) => $mail->psaId === '00123');
});
test('PSA ID image is matched by the ID prefix and attached to the email', function (): void {
    $mail = new UnregisteredPsaId('4741', 'Monterola');

    $locator = app(PsaIdCardLocator::class);
    $url = $locator->urlFor('4741');
    expect($locator->pathFor('4741'))
        ->toContain('4741_FRANCIS PIERRE J MONTEROLA.png')
        ->and(filter_var($url, FILTER_VALIDATE_URL))->not->toBeFalse()
        ->and($url)->toContain('FRANCIS%20PIERRE')
        ->and($mail->attachments())->toHaveCount(1)
        ->and($mail->content()->with['hasIdCard'])->toBeTrue();
});

test('manually uploaded card paths resolve for preview and attachment', function (): void {
    Storage::fake('psa_id_cards');
    Storage::disk('psa_id_cards')->put('uploaded-card.png', 'card-image');
    $recipient = UnregisteredPsaRecipient::create([
        'psa_id' => 'CUSTOM01',
        'full_name' => 'Manual Recipient',
        'email' => 'manual@example.test',
        'id_card_path' => 'uploaded-card.png',
    ]);

    PsaIdCardLocator::flush();
    $locator = app(PsaIdCardLocator::class);
    expect($locator->pathFor($recipient->psa_id))->toBe(Storage::disk('psa_id_cards')->path('uploaded-card.png'))
        ->and((new UnregisteredPsaId($recipient->psa_id, $recipient->full_name))->attachments())->toHaveCount(1);
});
