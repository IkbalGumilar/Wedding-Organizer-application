<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialAuthenticationController;
use App\Http\Controllers\WeddingPackageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/tentang-kami', 'public.about')->name('about');
Route::get('/paket-wedding', [WeddingPackageController::class, 'index'])->name('packages.index');
Route::get('/paket-wedding/{weddingPackage:slug}', [WeddingPackageController::class, 'show'])->name('packages.show');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::view('/kontak', 'public.contact')->name('contact');

Route::get('/auth/{provider}/redirect', [SocialAuthenticationController::class, 'redirect'])->name('social.redirect');
Route::match(['get', 'post'], '/auth/apple/link/callback', [SocialAuthenticationController::class, 'appleLinkCallback'])->name('social.apple-link.callback');
Route::match(['get', 'post'], '/auth/{provider}/callback', [SocialAuthenticationController::class, 'callback'])->name('social.callback');

Route::middleware(['auth', 'auth.session'])->group(function (): void {
    Route::get('/dashboard', [BookingController::class, 'index'])->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/lewati', [SocialAuthenticationController::class, 'skipProfileCompletion'])->name('social.profile.skip');
    Route::get('/auth/apple/link/complete/{token}', [SocialAuthenticationController::class, 'showAppleLinkCompletion'])->name('social.apple-link.complete');
    Route::post('/auth/apple/link/complete', [SocialAuthenticationController::class, 'confirmAppleLinkCompletion'])->name('social.apple-link.confirm');
    Route::get('/booking', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/booking/create/{weddingPackage:slug}', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/booking/review', [BookingController::class, 'review'])->name('bookings.review');
    Route::post('/booking/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::get('/booking/availability', [BookingController::class, 'availability'])->name('bookings.availability');
    Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('bookings.show');
});
