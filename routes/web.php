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
Route::view('/Event-Registration', 'pages.Event-Registration.events-registration')->name('Event-Registration');


Route::view('/Forloop', 'pages.Login.forloop')->name('forloop');
Route::view('/Login', 'pages.Login.login')->name('Login');