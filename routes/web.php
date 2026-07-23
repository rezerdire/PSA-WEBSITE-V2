<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RegistrationPdfController;
use App\Models\GalleryDay;
use App\Models\GalleryEvent;
use App\Models\GalleryImage;


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