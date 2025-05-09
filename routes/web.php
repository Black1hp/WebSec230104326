<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProductsController;
use App\Http\Controllers\Web\UsersController;
use App\Http\Controllers\Web\GameController;
use App\Http\Controllers\Web\GradesController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

// Public Routes
Route::get('/', function () {
    $email = emailFromLoginCertificate();
    if($email && !auth()->user()) {
        $user = User::where('email', $email)->first();
        if($user) Auth::setUser($user);
    }
    return view('welcome');
})->name('home')->middleware(['cache.headers:public;max_age=300;etag']);

// Public Product Route - Available to all visitors
Route::get('products', [ProductsController::class, 'list'])->middleware(['cache.headers:public;max_age=300;etag'])
    ->name('products_list');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('register', [UsersController::class, 'register'])->name('register');
    Route::post('register', [UsersController::class, 'doRegister'])->name('do_register');
    Route::get('login', [UsersController::class, 'login'])->name('login');
    Route::post('login', [UsersController::class, 'doLogin'])->name('do_login');

    // Google Authentication
    Route::get('/auth/google', [UsersController::class, 'redirectToGoogle'])->name('login_with_google');
    Route::get('/auth/google/callback', [UsersController::class, 'handleGoogleCallback'])->name('google.callback');

    // GitHub Authentication
    Route::get('/auth/github', [UsersController::class, 'redirectToGithub'])->name('login_with_github');
    Route::get('/auth/callback', [UsersController::class, 'handleGithubCallback'])->name('github.callback');

    // Password Reset
    Route::get('forgot-password', [UsersController::class, 'forgotPassword'])->name('password.request');
    Route::post('forgot-password', [UsersController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('reset-password/{token}', [UsersController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [UsersController::class, 'resetPassword'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

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

// Protected Routes (require authentication and email verification)
Route::middleware(['auth', 'verified'])->group(function () {
    // Product Likes
    Route::post('products/{product}/toggle-like', [ProductsController::class, 'toggleProductLike'])->name('product_toggle_like');
    // User Management
    Route::get('users', [UsersController::class, 'list'])->name('users');
    Route::get('profile/{user?}', [UsersController::class, 'profile'])->name('profile');
    Route::get('users/edit/{user?}', [UsersController::class, 'edit'])->name('users_edit');
    Route::post('users/save/{user}', [UsersController::class, 'save'])->name('users_save');
    Route::get('users/delete/{user}', [UsersController::class, 'delete'])->name('users_delete');
    Route::get('users/edit_password/{user?}', [UsersController::class, 'editPassword'])->name('edit_password');
    Route::post('users/save_password/{user}', [UsersController::class, 'savePassword'])->name('save_password');
    Route::get('users/create_employee', [UsersController::class, 'createEmployee'])->name('create_employee');
    Route::post('users/store_employee', [UsersController::class, 'storeEmployee'])->middleware(['throttle:5,1'])->name('store_employee');
    Route::get('users/purchases/{user}', [UsersController::class, 'userPurchases'])->name('user_purchases');
    Route::get('users/charge_credit/{user}', [UsersController::class, 'chargeCredit'])->name('charge_credit');
    Route::post('users/save_credit/{user}', [UsersController::class, 'saveCredit'])->name('save_credit');
    Route::post('users/give-gift/{user}', [UsersController::class, 'giveGift'])->name('give_gift');
    Route::get('users/reset-roles', [UsersController::class, 'resetRolesAndPermissions'])->name('reset_roles');

    // Protected Product Management Routes
    Route::get('products/edit/{product?}', [ProductsController::class, 'edit'])->name('products_edit');
    Route::post('products/save/{product?}', [ProductsController::class, 'save'])->middleware(['throttle:5,1'])->name('products_save');
    Route::get('products/delete/{product}', [ProductsController::class, 'delete'])->name('products_delete');
    Route::post('products/purchase/{product}', [ProductsController::class, 'purchase'])->name('products_purchase');
    Route::get('my-purchases', [ProductsController::class, 'myPurchases'])->name('my_purchases');

    // Games
    Route::prefix('games')->group(function () {
        Route::get('/', [GameController::class, 'index'])->name('games.index');
        Route::get('/create', [GameController::class, 'create'])->name('games.create');
        Route::get('/edit/{game?}', [GameController::class, 'edit'])->name('games.edit');
        Route::post('/save/{game?}', [GameController::class, 'save'])->name('games.save');
        Route::delete('/{game}', [GameController::class, 'delete'])->name('games.delete');
        Route::get('/{game}', [GameController::class, 'show'])->name('games.show');
    });

    // Grades
    Route::prefix('grades')->group(function () {
        Route::get('/', [GradesController::class, 'index'])->name('grades.index');
        Route::get('/create', [GradesController::class, 'edit'])->name('grades.create');
        Route::get('/edit/{grade?}', [GradesController::class, 'edit'])->name('grades.edit');
        Route::post('/save/{grade?}', [GradesController::class, 'save'])->name('grades.save');
        Route::delete('/{grade}', [GradesController::class, 'delete'])->name('grades.delete');
        Route::get('/{grade}', [GradesController::class, 'show'])->name('grades.show');
    });

    // Logout
    Route::get('logout', [UsersController::class, 'doLogout'])->name('do_logout');
});

// Game Routes (public)
Route::get('/multiplication-table', fn() => view('welcome', ['j' => 5]))->name('multiplication.table');
Route::get('/multable', function (Request $request) {
    $j = $request->number??5;
    $msg = $request->msg;
    return view('multable', compact("j", "msg"));
});
Route::get('/even', fn() => view('even'))->name('even');
Route::get('/prime', fn() => view('prime'))->name('prime');
Route::get('/test', fn() => view('test'))->name('test');


//Route::get('sqli', function (Request $request) {
//    $table = $request->query('table');
//    DB::unprepared("DROP TABLE IF EXISTS `{$table}`");
//    return redirect('/');
//});

//Route::get('collect', function(Request $request){
//    $name = $request->query('name');
//    $credit = $request->query('credit');
//    return response("data collected", 200)
//        ->header('Access-Control-Allow-Origin', '*')
//        ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
//        ->header('Access-control-Allow-Headers', 'X-Requested-With, Content-Type, Accept');
//});

