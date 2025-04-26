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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

// Public routes with web middleware for CSRF protection
Route::middleware(['web'])->group(function () {
    // Authentication routes with throttling
    Route::get('register', [UsersController::class, 'register'])
        ->middleware('guest')
        ->name('register');
    
    Route::post('register', [UsersController::class, 'doRegister'])
        ->middleware(['guest', 'throttle:5,1'])
        ->name('do_register');
    
    Route::get('login', [UsersController::class, 'login'])
        ->middleware('guest')
        ->name('login');
    
    Route::post('login', [UsersController::class, 'doLogin'])
        ->middleware(['guest', 'throttle:5,1'])
        ->name('do_login');
    
    Route::get('logout', [UsersController::class, 'doLogout'])
        ->middleware('auth')
        ->name('do_logout');
});

// OAuth Authentication Routes with security middleware
Route::middleware(['web', 'guest', 'throttle:10,1'])->group(function () {
    // Google Authentication Routes
    Route::get('/auth/google', [UsersController::class, 'redirectToGoogle'])
        ->name('login_with_google');
    
    Route::get('/auth/google/callback', [UsersController::class, 'handleGoogleCallback'])
        ->name('google.callback');

    // Github Authentication Routes
    Route::get('/auth/github', [UsersController::class, 'redirectToGithub'])
        ->name('login_with_github');
    
    Route::get('/auth/callback', [UsersController::class, 'handleGithubCallback'])
        ->name('github.callback');
});




// Email Verification Routes with enhanced security
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/email/verify', function () {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to verify your email.');
        }
        return view('auth.verify-email');
    })
    ->middleware(['cache.headers:private;max_age=0;must_revalidate'])
    ->name('verification.notice');

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
    })
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to request a verification link.');
        }

        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent!');
    })
    ->middleware('throttle:6,1')
    ->name('verification.send');
});

// Protected Routes (require email verification)
Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // User profile and standard user routes
    Route::get('profile/{user?}', [UsersController::class, 'profile'])
        ->name('profile');
    
    // User management routes - require password confirmation for sensitive actions
    Route::middleware(['password.confirm'])->group(function() {
        Route::post('users/save_password/{user}', [UsersController::class, 'savePassword'])
            ->middleware('throttle:5,1')
            ->name('save_password');
            
        Route::get('users/edit_password/{user?}', [UsersController::class, 'editPassword'])
            ->name('edit_password');
    });
    
    // Standard user routes with throttling for POST actions
    Route::get('users/edit/{user?}', [UsersController::class, 'edit'])
        ->name('users_edit');
        
    Route::post('users/save/{user}', [UsersController::class, 'save'])
        ->middleware('throttle:5,1')
        ->name('users_save');
        
    Route::get('users/purchases/{user}', [UsersController::class, 'userPurchases'])
        ->name('user_purchases');
        
    Route::get('users/charge_credit/{user}', [UsersController::class, 'chargeCredit'])
        ->name('charge_credit');
        
    Route::post('users/save_credit/{user}', [UsersController::class, 'saveCredit'])
        ->middleware('throttle:5,1')
        ->name('save_credit');
        
    Route::post('users/give-gift/{user}', [UsersController::class, 'giveGift'])
        ->middleware('throttle:5,1')
        ->name('give_gift');
    
    // Admin-only routes with IP restrictions (adjust IPs as needed)
    Route::middleware(function ($request, $next) {
        $allowedIps = ['127.0.0.1', '::1']; // Add your admin IPs
        if (!in_array($request->ip(), $allowedIps)) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    })->group(function () {
        Route::get('users', [UsersController::class, 'list'])
            ->name('users');
            
        Route::get('users/delete/{user}', [UsersController::class, 'delete'])
            ->name('users_delete');
            
        Route::get('users/create_employee', [UsersController::class, 'createEmployee'])
            ->name('create_employee');
            
        Route::post('users/store_employee', [UsersController::class, 'storeEmployee'])
            ->middleware('throttle:5,1')
            ->name('store_employee');
    });
});

// Product routes with appropriate protection
Route::middleware(['web'])->group(function () {
    // Public product listing
    Route::get('products', [ProductsController::class, 'list'])
        ->middleware(['cache.headers:public;max_age=300;etag'])
        ->name('products_list');
    
    // Admin product management routes with IP restrictions
    Route::middleware(['auth', 'verified', function ($request, $next) {
        $allowedIps = ['127.0.0.1', '::1']; // Add your admin IPs
        if (!in_array($request->ip(), $allowedIps)) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }])->group(function () {
        Route::get('products/edit/{product?}', [ProductsController::class, 'edit'])
            ->name('products_edit');
            
        Route::post('products/save/{product?}', [ProductsController::class, 'save'])
            ->middleware('throttle:5,1')
            ->name('products_save');
            
        Route::get('products/delete/{product}', [ProductsController::class, 'delete'])
            ->name('products_delete');
    });
    
    // Authenticated user product interactions
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::post('products/purchase/{product}', [ProductsController::class, 'purchase'])
            ->middleware('throttle:10,1')
            ->name('products_purchase');
            
        Route::get('my-purchases', [ProductsController::class, 'myPurchases'])
            ->name('my_purchases');
            
        Route::post('products/return/{purchase}', [ProductsController::class, 'returnProduct'])
            ->middleware('throttle:10,1')
            ->name('products_return');
            
        Route::post('products/like/{purchase}', [ProductsController::class, 'toggleLike'])
            ->middleware('throttle:10,1')
            ->name('products_toggle_like');
            
        Route::post('products/{product}/like', [ProductsController::class, 'toggleProductLike'])
            ->middleware('throttle:10,1') 
            ->name('product_toggle_like');
    });
});

// Public routes with security headers and caching
Route::middleware(['web'])->group(function () {
    Route::get('/', function () {
        return response(view('welcome'))
            ->header('X-Frame-Options', 'DENY')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Referrer-Policy', 'strict-origin-when-cross-origin');
    })->middleware(['cache.headers:public;max_age=300;etag']);

    // Routes with input validation
    Route::get('/multable', function (Request $request) {
        // Validate input
        $validated = $request->validate([
            'number' => 'nullable|integer|min:1|max:100',
            'msg' => 'nullable|string|max:255'
        ]);
        
        $j = $validated['number'] ?? 5;
        $msg = $validated['msg'] ?? null;
        
        return view('multable', compact("j", "msg"));
    })->middleware(['throttle:30,1']);

    // Static pages with caching
    Route::middleware(['cache.headers:public;max_age=3600;etag'])->group(function () {
        Route::get('/even', function () {
            return view('even');
        });

        Route::get('/prime', function () {
            return view('prime');
        });
    });

    // Test route - only available in local environment
    Route::get('/test', function () {
        if (app()->environment('production')) {
            abort(404);
        }
        return view('test');
    });
});

// Password Reset Routes with enhanced security
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('forgot-password', [UsersController::class, 'forgotPassword'])
        ->middleware(['cache.headers:no-store,private'])
        ->name('password.request');

    Route::post('forgot-password', [UsersController::class, 'sendResetLink'])
        ->middleware(['throttle:5,1'])
        ->name('password.email');

    Route::get('reset-password/{token}', [UsersController::class, 'showResetForm'])
        ->middleware(['cache.headers:no-store,private', 'throttle:10,1'])
        ->name('password.reset');

    Route::post('reset-password', [UsersController::class, 'resetPassword'])
        ->middleware(['throttle:5,1'])
        ->name('password.update');
});

// Global security headers for all routes
Route::middleware(['web'])->group(function ($router) {
    $router->pushMiddlewareToGroup('web', function ($request, $next) {
        $response = $next($request);
        
        if (method_exists($response, 'header')) {
            $response->header('X-Frame-Options', 'DENY');
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            
            // Only in production
            if (app()->environment('production')) {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
                $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'");
            }
        }
        
        return $response;
    });
});
