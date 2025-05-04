<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

abstract class Controller extends \Illuminate\Routing\Controller
{
    protected function requireAuth() {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }
        return true;
    }

    protected function checkRateLimit($key, $maxAttempts=5, $minutes=1) {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            abort(429, 'Too many attempts. Please try again later.');
        }
        RateLimiter::hit($key, $minutes * 60);
    }
}
