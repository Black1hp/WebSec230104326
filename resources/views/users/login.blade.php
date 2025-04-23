@extends('layouts.master')
@section('title', 'Login')
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
<div class="d-flex justify-content-center">
  <div class="card m-4 col-sm-6">
    <div class="card-body">
      <form action="{{route('do_login')}}" method="post">
      {{ csrf_field() }}
      <div class="form-group">
        @foreach($errors->all() as $error)
        <div class="alert alert-danger">
          <strong>Error!</strong> {{$error}}
        </div>
        @endforeach
      </div>
      <div class="form-group mb-2">
        <label for="model" class="form-label">Email:</label>
        <input type="email" class="form-control" placeholder="email" name="email" required>
      </div>
      <div class="form-group mb-2">
        <label for="model" class="form-label">Password:</label>
        <input type="password" class="form-control" placeholder="password" name="password" required>
      </div>
      <div class="form-group mb-2">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
      </div>
      <div class="form-group mb-2">
        <button type="submit" class="btn btn-primary">Login</button>
        <a href="{{ route('password.request') }}" class="btn btn-link">Forgot Your Password?</a>
      </div>
      <div class="form-group mb-2">
        <p class="text-center">Or</p>
        <a href="{{ route('login_with_google') }}" class="btn btn-danger w-100">
          <i class="fab fa-google"></i> Login with Google
        </a>
      </div>
      <div class="form-group mb-2">
        <p class="text-center">Or</p>
        <a href="{{ route('login_with_github') }}" class="btn btn-primary w-100">
          <i class="fab fa-github"></i> Login with Github
        </a>
      </div>
    </form>
    </div>
  </div>
</div>
@endsection
