<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingStatusController;
use App\Http\Controllers\CarUnitController;
use App\Http\Controllers\RentalController;
use App\Http\Middleware\EnsureAdmin;
use App\Services\RentalQuote;
use Illuminate\Support\Facades\Route;

Route::get('/', [RentalController::class, 'home'])->name('home');
Route::get('/cari-mobil', [RentalController::class, 'catalog'])->name('catalog');
Route::get('/detail-mobil/{car?}', [RentalController::class, 'detail'])->name('detail');
Route::get('/detail-mobil/{car}/unit-tersedia', [RentalController::class, 'unitAvailability'])->middleware('throttle:60,1')->name('units.availability');
Route::post('/reservasi', [RentalController::class, 'reserve'])->name('reserve');
Route::get('/booking-checkout', [RentalController::class, 'checkout'])->name('checkout');
Route::post('/booking-checkout', [RentalController::class, 'store'])->middleware('throttle:20,1')->name('booking.store');
Route::get('/booking-berhasil/{code?}', [RentalController::class, 'confirmation'])->name('confirmation');
Route::get('/pesanan-saya', [BookingStatusController::class, 'index'])->name('orders');
Route::get('/status-riwayat', [BookingStatusController::class, 'index'])->name('booking-status');
Route::post('/status-riwayat', [BookingStatusController::class, 'lookup'])->middleware('throttle:15,1')->name('booking-status.lookup');
Route::post('/status-riwayat/tutup', [BookingStatusController::class, 'close'])->name('booking-status.close');
Route::get('/kalender/{code}', [RentalController::class, 'calendar'])->name('calendar');
Route::get('/favorit', fn () => view('pages.favorites', ['cars' => app(RentalQuote::class)->cars()]))->name('favorites');
Route::get('/informasi/{page}', [RentalController::class, 'info'])->name('info');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:15,1')->name('login.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::prefix('admin')->name('admin.')->middleware(['auth', 'auth.session', EnsureAdmin::class])->group(function (): void {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/armada', [AdminController::class, 'cars'])->name('cars');
    Route::get('/armada/tambah', [AdminController::class, 'createCar'])->name('cars.create');
    Route::post('/armada', [AdminController::class, 'storeCar'])->name('cars.store');
    Route::get('/armada/{car}/edit', [AdminController::class, 'editCar'])->name('cars.edit');
    Route::put('/armada/{car}', [AdminController::class, 'updateCar'])->name('cars.update');
    Route::get('/armada/{car}/unit', [CarUnitController::class, 'index'])->name('units');
    Route::post('/armada/{car}/unit', [CarUnitController::class, 'store'])->name('units.store');
    Route::get('/armada/{car}/unit/{unit}/edit', [CarUnitController::class, 'edit'])->name('units.edit');
    Route::put('/armada/{car}/unit/{unit}', [CarUnitController::class, 'update'])->name('units.update');
    Route::get('/reservasi', [AdminController::class, 'bookings'])->name('bookings');
    Route::get('/reservasi/{booking}', [AdminController::class, 'booking'])->name('booking');
    Route::put('/reservasi/{booking}', [AdminController::class, 'updateBooking'])->name('booking.update');
    Route::get('/akun', [AdminController::class, 'account'])->name('account');
    Route::put('/akun/kata-sandi', [AdminController::class, 'updatePassword'])->name('password.update');
});
