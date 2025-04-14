<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProductsController;
use App\Http\Controllers\Web\UsersController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('register', [UsersController::class, 'register'])->name('register');
Route::post('register', [UsersController::class, 'doRegister'])->name('do_register');
Route::get('login', [UsersController::class, 'login'])->name('login');
Route::post('login', [UsersController::class, 'doLogin'])->name('do_login');
Route::get('logout', [UsersController::class, 'doLogout'])->name('do_logout');

// Google Authentication Routes
Route::get('/auth/google', [UsersController::class, 'redirectToGoogle'])->name('login_with_google');
Route::get('/auth/google/callback', [UsersController::class, 'handleGoogleCallback'])->name('google.callback');

// Email Verification Routes
Route::get('/email/verify', function () {
    if (!auth()->check()) {
        return redirect()->route('login')
            ->with('error', 'Please log in to verify your email.');
    }
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id) {
    $user = User::find($id);
    
    if (!$user) {
        return redirect()->route('login')
            ->with('error', 'Invalid verification link.');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect()->route('login')
            ->with('success', 'Email already verified! Please login.');
    }

    if (!hash_equals(sha1($user->getEmailForVerification()), $request->hash)) {
        return redirect()->route('login')
            ->with('error', 'Invalid verification link.');
    }

    $user->markEmailAsVerified();
    event(new Verified($user));

    return redirect()->route('login')
        ->with('success', 'Email verified successfully! You can now log in.');
})->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if (!auth()->check()) {
        return redirect()->route('login')
            ->with('error', 'Please log in to request a verification link.');
    }

    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'Verification link sent!');
})->middleware('throttle:6,1')->name('verification.send');

// Protected Routes (require email verification)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('users', [UsersController::class, 'list'])->name('users');
    Route::get('profile/{user?}', [UsersController::class, 'profile'])->name('profile');
    Route::get('users/edit/{user?}', [UsersController::class, 'edit'])->name('users_edit');
    Route::post('users/save/{user}', [UsersController::class, 'save'])->name('users_save');
    Route::get('users/delete/{user}', [UsersController::class, 'delete'])->name('users_delete');
    Route::get('users/edit_password/{user?}', [UsersController::class, 'editPassword'])->name('edit_password');
    Route::post('users/save_password/{user}', [UsersController::class, 'savePassword'])->name('save_password');
    Route::get('users/create_employee', [UsersController::class, 'createEmployee'])->name('create_employee');
    Route::post('users/store_employee', [UsersController::class, 'storeEmployee'])->name('store_employee');
    Route::get('users/purchases/{user}', [UsersController::class, 'userPurchases'])->name('user_purchases');
    Route::get('users/charge_credit/{user}', [UsersController::class, 'chargeCredit'])->name('charge_credit');
    Route::post('users/save_credit/{user}', [UsersController::class, 'saveCredit'])->name('save_credit');
    Route::post('users/give-gift/{user}', [UsersController::class, 'giveGift'])->name('give_gift');
});

Route::get('products', [ProductsController::class, 'list'])->name('products_list');
Route::get('products/edit/{product?}', [ProductsController::class, 'edit'])->name('products_edit');
Route::post('products/save/{product?}', [ProductsController::class, 'save'])->name('products_save');
Route::get('products/delete/{product}', [ProductsController::class, 'delete'])->name('products_delete');
Route::post('products/purchase/{product}', [ProductsController::class, 'purchase'])->name('products_purchase');
Route::get('my-purchases', [ProductsController::class, 'myPurchases'])->name('my_purchases');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/multable', function (Request $request) {
    $j = $request->number??5;
    $msg = $request->msg;
    return view('multable', compact("j", "msg"));
});

Route::get('/even', function () {
    return view('even');
});

Route::get('/prime', function () {
    return view('prime');
});

Route::get('/test', function () {
    return view('test');
});

// Password Reset Routes
Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [UsersController::class, 'forgotPassword'])
        ->name('password.request');

    Route::post('forgot-password', [UsersController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')  // 5 attempts per minute
        ->name('password.email');

    Route::get('reset-password/{token}', [UsersController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('reset-password', [UsersController::class, 'resetPassword'])
        ->middleware('throttle:5,1')  // 5 attempts per minute
        ->name('password.update');
});
