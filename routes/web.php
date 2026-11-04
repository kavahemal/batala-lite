<?php

use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CylinderBrandController;
use App\Http\Controllers\CylinderTypeController;
use App\Http\Controllers\FeaturePreviewController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Reports\StockReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');

    Route::get('profile', [ProfileController::class, 'index'])->name('profile.view');
    Route::put('profile', [ProfileController::class, 'profileUpdate'])->name('profile.update');
    Route::get('change-password', [ProfileController::class, 'changePassword'])->name('change-password.view');
    Route::put('change-password', [ProfileController::class, 'changePasswordUpdate'])->name('change-password.update');
    // Before the resource route, so "check-mobile" isn't read as a customer id
    Route::get('customers/check-mobile', [CustomerController::class, 'checkMobile'])->name('customers.check-mobile');
    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('cylinder-brands', CylinderBrandController::class)->except('show');
    Route::resource('cylinder-types', CylinderTypeController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('booking', BookingController::class);
    Route::post('booking/{booking}/delivered', [BookingController::class, 'markDelivered'])->name('booking.delivered');
    Route::resource('purchase', PurchaseController::class);

    // Full-version modules ('locked' in config/permissions.php): description + screenshots
    Route::get('full-version/{module}', FeaturePreviewController::class)->name('preview');

    // Reports
    Route::get('stock-report', [StockReportController::class, 'index'])->middleware('can:stock-report.index')->name('stock-report.index');

    Route::middleware(['role:admin|owner'])->group(function () {
        Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');
        Route::get('role-permissions/{role}/edit', [RolePermissionController::class, 'edit'])->name('role-permissions.edit');
        Route::post('role-permissions/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])->name('role-permissions.updatePermissions');
    });
});
