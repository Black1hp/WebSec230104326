<?php

use App\Http\Controllers\Web\GradesController;
use App\Http\Controllers\Web\ProductsController;
use App\Http\Controllers\Web\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/multiplication-table?', function () {
    $j = 5; // or any other value you want to pass
    return view('welcome', compact('j'));
});

Route::get('/even', function () {
    return view('even');
});

Route::get('/multable', function () {
    $j = 5;
    return view('multable', compact('j'));
});

// Add route for prime numbers
Route::get('/prime', function () {
    return view('prime');
});

/* Authentication Routes */
Route::middleware('guest')->group(function () {
    Route::get('/login', [UsersController::class, 'showLogin'])->name('login');
    Route::post('/login', [UsersController::class, 'login']);
    Route::get('/register', [UsersController::class, 'showRegister'])->name('register');
    Route::post('/register', [UsersController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [UsersController::class, 'logout'])->name('logout');
    
    // Profile Routes
    Route::get('/profile', [UsersController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [UsersController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [UsersController::class, 'updatePassword'])->name('profile.password');

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('users', [UsersController::class, 'index'])->name('users.index');
        Route::get('users/edit/{user?}', [UsersController::class, 'edit'])->name('users.edit');
        Route::post('users/save/{user?}', [UsersController::class, 'save'])->name('users.save');
        Route::delete('users/delete/{user}', [UsersController::class, 'delete'])->name('users.delete');
    });
});

/* Start Products */
Route::middleware('auth')->group(function () {
    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('products/edit/{product?}', [ProductsController::class, 'edit'])->name('products.edit');
    Route::post('products/save/{product?}', [ProductsController::class, 'save'])->name('products.save');
    Route::get('products/delete/{product}', [ProductsController::class, 'delete'])->name('products.delete');
});

/* Start Grades */
Route::middleware('auth')->group(function () {
    Route::get('/grades', [GradesController::class, 'index'])->name('grades.index');
    Route::get('grades/edit/{grade?}', [GradesController::class, 'edit'])->name('grades.edit');
    Route::post('grades/save/{grade?}', [GradesController::class, 'save'])->name('grades.save');
    Route::delete('/delete/{grade}', [GradesController::class, 'delete'])->name('grades.delete');
});
