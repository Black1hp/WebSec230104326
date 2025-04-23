@extends('layouts.master')
@section('title', 'Login')
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-papmU1Xn3q0w6Vf1Q5Jv2kK4Xh9kQ2Q6h7+8R8yQ6Q5Jv2kK4Xh9kQ2Q6h7+8R8yQ6Q5Jv2kK4Xh9kQ2Q6h7+8R8yQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center bg-light">
  <div class="card shadow rounded-4 p-4" style="max-width: 400px; width:100%;">
    <div class="text-primary text-center fs-2 fw-bold mb-3">
      <i class="fas fa-user-circle me-2"></i>Login
    </div>
    <form action="{{ route('do_login') }}" method="post" autocomplete="off" aria-label="Login form">
      {{ csrf_field() }}
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required aria-required="true" autofocus>
      </div>
      <div class="mb-3 position-relative">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required aria-required="true">
        
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">Remember Me</label>
      </div>
      <div class="mb-3">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
      </div>
      <div class="d-grid gap-2 mb-2">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-sign-in-alt me-1"></i> Login
        </button>
      </div>
      <div class="text-center mb-2">
        <a href="{{ route('password.request') }}" class="small">Forgot Your Password?</a>
      </div>
      <div class="text-center mb-2">
        <span class="text-muted">Or</span>
      </div>
      <div class="d-grid gap-2 mb-2">
        <a href="{{ route('login_with_google') }}" class="btn btn-danger">
          <i class="fab fa-google me-1"></i> Login with Google
        </a>
        <a href="{{ route('login_with_github') }}" class="btn btn-dark">
          <i class="fab fa-github me-1"></i> Login with Github
        </a>
      </div>
      <div class="text-center mt-3">
        <span class="small">Don't have an account?</span>
        <a href="{{ route('register') }}" class="small">Register</a>
      </div>
    </form>
  </div>
</div>

@endsection
