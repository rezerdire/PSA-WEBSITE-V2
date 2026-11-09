<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RegistrationPdfController;
use App\Models\GalleryDay;
use App\Models\GalleryEvent;
use App\Models\GalleryImage;
use App\Livewire\BoothScan;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/registrations-export-pdf', [RegistrationPdfController::class, 'export'])
        ->name('admin.registrations.export-pdf');
});



Route::get('/info-scan-qr', function () {
    return view('pages.qr.info-scanqr');
})->name('info-scan-qr');
 
Route::get('/Gallery', function () {
    $events = GalleryEvent::with('days')->get();

    $events->each(function ($event) {
        $dayIds = $event->days->pluck('id');

        $event->previewImages = GalleryImage::whereHas('category', function ($q) use ($dayIds) {
            $q->whereIn('gallery_day_id', $dayIds);
        })->inRandomOrder()->limit(8)->get();
    });

    return view('pages.Gallery.index', compact('events'));
})->name('Gallery');

Route::get('/gallery/{event:slug}/{day:slug}', function (GalleryEvent $event, GalleryDay $day) {
    return view('pages.Gallery.gallery', compact('event', 'day'));
})->name('gallery.day')->scopeBindings();


Route::view('/', 'pages.Home.index')->name('home');
Route::view('/About-Us/Office-and-board', 'pages.AboutUs.office-and-board')->name('Office-and-board');
Route::view('/About-Us/SubSpecialty-SIG', 'pages.AboutUs.subspecialty-sig')->name('SubSpecialty-SIG');
Route::view('/Chapter-Presidents', 'pages.AboutUs.chapter-presidents')->name('Chapter-Presidents');
Route::view('/Legacy', 'pages.AboutUs.legacy')->name('Legacy');
Route::view('/Membership', 'pages.Membership.member-registation')->name('Membership');
Route::view('/AnnualConvention2026/Registration', 'pages.Event-Registration.events-registration')->name('Event-Registration');
Route::view('/CME/PJA', 'pages.CME.pja')->name('pja');  
Route::view('/AnnualConvention2026', 'pages.CME.convention')->name('convention');
Route::view('/CME/Mid-Year-Convention', 'pages.CME.mid-year-convention')->name('mid-year-convention');
Route::view('/CME/Interesting-Case', 'pages.CME.InterestingCase')->name('Interesting-Case');

// LOGIN REMOVE

Route::get('/sim-wars', function () {
    return view('sim_wars/sim_wars');
})->name('sim-wars');


// PSA HYMN
Route::get('/psa-hymn', function () {
    return view('pages.AboutUs.pagehymn');
})->name('psa-hymn');


// Midyear Convention 2026
// file: midyearconvention
Route::view('/CME/Mid-Year-Convention/Poster', 'pages.CME.midyearconvention.poster')->name('midyearconvention-poster');
Route::view('/CME/Mid-Year-Convention/Workshop', 'pages.CME.midyearconvention.workshop')->name('midyearconvention-workshop');
Route::view('/CME/Mid-Year-Convention/SocialProgram', 'pages.CME.midyearconvention.soicalprogram')->name('midyearconvention-socialprogram');
Route::view('/CME/Mid-Year-Convention/Tour&Accomodation', 'pages.CME.midyearconvention.touraccomodation')->name('midyearconvention-touraccomodation');
Route::view('/CME/Mid-Year-Convention/PickleballTournament', 'pages.CME.midyearconvention.pickleballtourna')->name('midyearconvention-pickleballtourna');
Route::view('/CME/Mid-Year-Convention/ScientificProgram', 'pages.CME.midyearconvention.scientificprogram')->name('midyearconvention-scientificprogram');
// mid year root 
Route::view('/CME/Mid-Year-Convention', 'pages::CME.mid-year-convention')->name('mid-year-convention');


// Annual Convention 2026
Route::view('CME/Annual-Convention/Poster', 'pages.CME.annualconvention.poster')->name('annual-convention-poster');
Route::view('/CME/Annual-Convention/ScientificProgram', 'pages.CME.annualconvention.scientificprogram')->name('annual-convention-scientific-program');

Route::view('/Recent-Events-List', 'pages.RecentEvent.recent-event-list')->name('recent-event-list');

Route::view('/MH-Registry','pages.mh-registry.mh-registry-page')->name('mh-registry');



Route::get('/booth/{booth_code}', function (string $booth_code) {
    return view('pages.booth_page.index', ['booth_code' => $booth_code]);
})->name('booth.scan');

// booth checker
Route::get('/booth-checker', function () {
    return view('pages.booth_page.checker');
})->name('booth.checker');


// WORKSHOP
Route::view('/CME/Workshop', 'pages.workshop.index')->name('workshop');

// TEMPORARY QR diagnostic — remove after debugging deployed Send QR (2026-11-09)
Route::get('/qr-diag', function (\Illuminate\Http\Request $request) {
    abort_unless($request->query('token') === 'psa-diag-5847', 403);

    $gd = extension_loaded('gd') ? gd_info() : [];

    $diskRoot = config('filesystems.disks.members_qr.root');

    $dirs = [];
    foreach (['member_id', 'new QR', 'qr'] as $d) {
        $dirs[$d] = is_dir($diskRoot . '/' . $d)
            ? count(array_diff(scandir($diskRoot . '/' . $d) ?: [], ['.', '..']))
            : 'MISSING';
    }

    $logPath = storage_path('logs/laravel.log');
    $logTail = 'no laravel.log';
    if (is_file($logPath)) {
        $lines = array_slice(file($logPath, FILE_IGNORE_NEW_LINES) ?: [], -80);
        $logTail = collect($lines)
            ->filter(fn ($l) => str_contains($l, 'ERROR') || str_contains($l, 'Exception'))
            ->take(-5)
            ->values()
            ->toArray();
    }

    return response()->json([
        'php'                => PHP_VERSION,
        'gd_loaded'          => extension_loaded('gd'),
        'gd_freetype'        => $gd['FreeType Support'] ?? false,
        'font_exists'        => is_file(public_path('fonts/Roboto-Bold.ttf')),
        'font_path'          => public_path('fonts/Roboto-Bold.ttf'),
        'members_qr_root'    => $diskRoot,
        'members_qr_dir_exists'   => is_dir($diskRoot),
        'members_qr_writable'     => is_writable($diskRoot),
        'card_files_on_disk'      => $dirs,
        'db_database'             => config('database.connections.' . config('database.default') . '.database'),
        'app_timezone'            => config('app.timezone'),
        'registration_model_file' => (new ReflectionClass(\App\Models\Registration::class))->getFileName(),
        'registration_has_latestQrSend' => method_exists(\App\Models\Registration::class, 'latestQrSend'),
        'registration_has_qrSends'      => method_exists(\App\Models\Registration::class, 'qrSends'),
        'qr_send_rows'            => \Illuminate\Support\Facades\Schema::hasTable('registration_qr_sends')
            ? \DB::table('registration_qr_sends')->count()
            : null,
        'qr_send_last3'           => \Illuminate\Support\Facades\Schema::hasTable('registration_qr_sends')
            ? \DB::table('registration_qr_sends')->orderByDesc('id')->limit(3)
                ->get(['id', 'registration_id', 'sent_at'])->toArray()
            : null,
        'relation_probe'          => (function () {
            $r = \App\Models\Registration::with('latestQrSend')->first();
            return $r ? [
                'ref'         => $r->id,
                'has_related' => $r->latestQrSend !== null,
                'sent_at'     => optional($r->latestQrSend)->sent_at?->format('Y-m-d H:i:s'),
            ] : 'no registrations';
        })(),
        'table_members_qr'          => \Illuminate\Support\Facades\Schema::hasTable('members_qr'),
        'table_registration_qr_sends' => \Illuminate\Support\Facades\Schema::hasTable('registration_qr_sends'),
        'members_qr_rows'           => \Illuminate\Support\Facades\Schema::hasTable('members_qr')
            ? \DB::table('members_qr')->count()
            : null,
        'last_errors'        => $logTail,
    ]);
});

// TEMPORARY QR import runner — remove after debugging (2026-11-09)
Route::get('/qr-run-import', function (\Illuminate\Http\Request $request) {
    abort_unless($request->query('token') === 'psa-diag-5847', 403);

    @set_time_limit(300);
    @ignore_user_abort(true);

    $exit = \Illuminate\Support\Facades\Artisan::call('members:import-qr');
    $output = \Illuminate\Support\Facades\Artisan::output();

    return response("<pre>" . e($output) . "</pre><p>Exit code: {$exit} — refresh to re-run (safe, resumes where it stopped)</p>");
});