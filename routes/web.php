<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
// Import Controller Pelanggan
use App\Http\Controllers\Pelanggan\HomeController;
use App\Http\Controllers\Pelanggan\BookingController;

Route::get('/phpinfo', fn() => dd(
    ini_get('post_max_size'),
    ini_get('upload_max_filesize')
));

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| PELANGGAN ROUTES (PUBLIC - TANPA LOGIN)
|--------------------------------------------------------------------------
*/
// Halaman Utama: Branding, Portfolio, & List Paket
Route::get('/', [HomeController::class, 'index'])->name('home');

// Detail Paket & Form Input Data Pengantin
Route::get('/package/{id}', [HomeController::class, 'showPackage'])->name('package.detail');

// API untuk cek tanggal penuh (dipanggil JavaScript di Laptop Pelanggan)
Route::get('/api/booked-dates/{package_id}', [BookingController::class, 'getFullyBookedDates']);

// Proses Simpan Booking (Guest Booking)
// Halaman booking (HARUS ada package_id)
Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');

// Simpan booking
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

// Halaman Invoice & Instruksi Bayar (Tampil setelah submit form)


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (DASHBOARD OWNER)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:owner'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function(){
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('packages', \App\Http\Controllers\Admin\PackageController::class);
    Route::get('/packages/{id}', [\App\Http\Controllers\Admin\PackageController::class, 'show'])->name('packages.show');
    Route::resource('portfolios', \App\Http\Controllers\Admin\PortfolioController::class);

    Route::get('bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{id}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('bookings/{id}/status', [\App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('bookings.updateStatus');
    Route::post('bookings/{id}/cancel', [\App\Http\Controllers\Admin\BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('transactions', [\App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/{id}', [\App\Http\Controllers\Admin\TransactionController::class, 'show'])->name('transactions.show');
    
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    Route::post('/admin/bookings/{id}/status', [BookingController::class, 'updateStatus'])
    ->name('admin.bookings.updateStatus');

    
});