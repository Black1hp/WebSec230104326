<?php

use App\Http\Controllers\Web\GradesController;
use App\Http\Controllers\Web\ProductsController;
use App\Http\Controllers\Web\UsersController;
use App\Http\Controllers\Web\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'))->name('home');

Route::get('/multiplication-table', fn() => view('welcome', ['j' => 5]))->name('multiplication.table');

Route::get('/even', fn() => view('even'))->name('even');

Route::get('/multable', fn() => view('multable', ['j' => 5]))->name('multable');

Route::get('/prime', fn() => view('prime'))->name('prime');

/* Authentication Routes */
Route::middleware('guest')->group(function () {
    Route::get('/login', [UsersController::class, 'showLogin'])->name('login');
    Route::post('/login', [UsersController::class, 'login'])->name('login.post');
    Route::get('/register', [UsersController::class, 'showRegister'])->name('register');
    Route::post('/register', [UsersController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [UsersController::class, 'logout'])->name('logout');

    Route::prefix('profile')->group(function () {
        Route::get('/', [UsersController::class, 'profile'])->name('profile');
        Route::post('/update', [UsersController::class, 'updateProfile'])->name('profile.update');
        Route::post('/password', [UsersController::class, 'updatePassword'])->name('profile.password');
        Route::get('/{user}', [UsersController::class, 'showUserProfile'])->name('profile.user');
    });
});

/* Admin Routes */
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('users.index');
        Route::get('/create', [UsersController::class, 'edit'])->name('users.create');
        Route::get('/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::post('/save/{user?}', [UsersController::class, 'save'])->name('users.save');
        Route::delete('/{user}', [UsersController::class, 'delete'])->name('users.delete');
        Route::get('/{user}', [UsersController::class, 'show'])->name('users.show');
    });
    
    // Credit management routes
    Route::get('/credit-management', [UsersController::class, 'creditManagement'])->name('admin.credit-management');
    Route::post('/users/{user}/add-credit', [UsersController::class, 'addCredit'])->name('admin.add-credit');
    Route::post('/users/{user}/update-credit', [UsersController::class, 'updateCredit'])->name('admin.update-credit');
});

/* Products Routes */
Route::prefix('products')->middleware('auth')->group(function () {
    Route::get('/', [ProductsController::class, 'index'])->name('products.index');

    // Employee routes - putting these BEFORE the general {product} route
    Route::middleware('employee')->group(function () {
        Route::get('/edit/{product?}', [ProductsController::class, 'edit'])->name('products.edit');
        Route::post('/save/{product?}', [ProductsController::class, 'save'])->name('products.save');
        Route::delete('/{product}', [ProductsController::class, 'delete'])->name('products.delete');
    });

    // This general catch-all route should come AFTER any specific routes
    Route::get('/{product}', [ProductsController::class, 'show'])->name('products.show');
});

/* Customer Routes */
Route::middleware(['auth'])->prefix('customer')->group(function () {
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::post('/purchase/{product}', [PurchaseController::class, 'store'])->name('purchases.store');
});

/* Employee Routes */
Route::middleware(['auth', 'employee'])->prefix('employee')->group(function () {
    Route::get('/customers', [UsersController::class, 'customerList'])->name('employee.customers');
    Route::get('/credit-management', [UsersController::class, 'creditManagement'])->name('employee.credit-management');
    Route::post('/customers/{user}/add-credit', [UsersController::class, 'addCredit'])->name('employee.add-credit');
    Route::post('/customers/{user}/update-credit', [UsersController::class, 'updateCredit'])->name('employee.update-credit');
});

/* Grades Routes */
Route::prefix('grades')->middleware('auth')->group(function () {
    Route::get('/', [GradesController::class, 'index'])->name('grades.index');
    Route::get('/create', [GradesController::class, 'edit'])->name('grades.create');
    Route::get('/edit/{grade?}', [GradesController::class, 'edit'])->name('grades.edit');
    Route::post('/save/{grade?}', [GradesController::class, 'save'])->name('grades.save');
    Route::delete('/{grade}', [GradesController::class, 'delete'])->name('grades.delete');
    Route::get('/{grade}', [GradesController::class, 'show'])->name('grades.show');
});
