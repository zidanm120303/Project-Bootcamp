<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\DisputeController as CustomerDisputeController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Mitra\BookingController as MitraBookingController;
use App\Http\Controllers\Mitra\DashboardController as MitraDashboardController;
use App\Http\Controllers\Mitra\ProductController as MitraProductController;
use App\Http\Controllers\Mitra\ProfileController as MitraProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSite\CatalogController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\ProductController as PublicProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/produk/{product}', [PublicProductController::class, 'show'])->name('products.show');
Route::post('/produk/{product}/ketersediaan', [PublicProductController::class, 'availability'])->name('products.availability');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
    Route::get('/checkout/{product}', [PublicProductController::class, 'checkout'])->name('checkout');
    Route::get('/pesanan', [CustomerBookingController::class, 'index'])->name('bookings.index');
    Route::post('/pesanan', [CustomerBookingController::class, 'store'])->name('bookings.store');
    Route::get('/pesanan/{booking}', [CustomerBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/pesanan/{booking}/cancel', [CustomerBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/pesanan/{booking}/pembayaran', [CustomerPaymentController::class, 'store'])->name('payments.store');
    Route::post('/pesanan/{booking}/ulasan', [CustomerReviewController::class, 'store'])->name('reviews.store');
    Route::post('/pesanan/{booking}/komplain', [CustomerDisputeController::class, 'store'])->name('disputes.store');
});

Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/dashboard', MitraDashboardController::class)->name('dashboard');
    Route::get('/profil', [MitraProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [MitraProfileController::class, 'update'])->name('profile.update');
    Route::post('/dokumen', [MitraProfileController::class, 'uploadDocument'])->name('documents.store');
    Route::resource('produk', MitraProductController::class)->parameters(['produk' => 'product'])->names('products')->except('show');
    Route::get('/pesanan', [MitraBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/pesanan/{booking}', [MitraBookingController::class, 'update'])->name('bookings.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/pengguna', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/pengguna/admin', [AdminUserController::class, 'admins'])->name('users.admin');
    Route::get('/pengguna/mitra', [AdminUserController::class, 'partners'])->name('users.mitra');
    Route::get('/pengguna/customer', [AdminUserController::class, 'customers'])->name('users.customer');
    Route::patch('/pengguna/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::get('/mitra', [AdminPartnerController::class, 'index'])->name('partners.index');
    Route::patch('/mitra/{partner}', [AdminPartnerController::class, 'update'])->name('partners.update');
    Route::resource('kategori', AdminCategoryController::class)->parameters(['kategori' => 'category'])->names('categories')->except(['create', 'show', 'edit']);
    Route::get('/produk', [AdminProductController::class, 'index'])->name('products.index');
    Route::patch('/produk/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::get('/booking', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::patch('/pembayaran/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');
    Route::get('/komplain', [AdminDisputeController::class, 'index'])->name('disputes.index');
    Route::patch('/komplain/{dispute}', [AdminDisputeController::class, 'update'])->name('disputes.update');
    Route::get('/pengaturan', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::patch('/pengaturan', [AdminSettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
