<?php

use App\Http\Controllers\Web\GameController;
use App\Http\Controllers\Web\GradesController;
use App\Http\Controllers\Web\ProductsController;
use App\Http\Controllers\Web\UsersController;
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
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('users.index');
        Route::get('/create', [UsersController::class, 'edit'])->name('users.create');
        Route::get('/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::post('/save/{user?}', [UsersController::class, 'save'])->name('users.save');
        Route::delete('/{user}', [UsersController::class, 'delete'])->name('users.delete');
        Route::get('/{user}', [UsersController::class, 'show'])->name('users.show');
    });
});

/* Products Routes */
Route::prefix('products')->middleware('auth')->group(function () {
    Route::get('/', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/edit/{product?}', [ProductsController::class, 'edit'])->name('products.edit');
    Route::put( '/save/{product?}', [ProductsController::class, 'save'])->name('products.save');
    Route::delete('/{product}', [ProductsController::class, 'delete'])->name('products.delete');
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


/* Games Routes */
Route::prefix('games')->middleware('auth')->group(function () {
    Route::get('/', [GameController::class, 'index'])->name('games.index');
    Route::get('/create', [GameController::class, 'create'])->name('games.create');
    Route::get('/edit/{game?}', [GameController::class, 'edit'])->name('games.edit');
    Route::post('/save/{game?}', [GameController::class, 'save'])->name('games.save');
    Route::delete('/{game}', [GameController::class, 'delete'])->name('games.delete');
    Route::get('/{game}', [GameController::class, 'show'])->name('games.show');
});
