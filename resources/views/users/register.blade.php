@extends('layouts.master')
@section('title', 'Register')
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
<div class="d-flex justify-content-center">
  <div class="card m-4 col-sm-6">
    <div class="card-body">
      <h3 class="card-title mb-4 text-center">Register Account</h3>
      <form action="{{route('do_register')}}" method="post">
        {{ csrf_field() }}
        
        @foreach($errors->all() as $error)
          <div class="alert alert-danger">
            <strong>Error!</strong> {{$error}}
          </div>
        @endforeach
        
        <div class="form-group mb-3">
          <label for="name" class="form-label">Name:</label>
          <small class="form-text text-muted">Enter your full name.</small>
          <input type="text" class="form-control" id="name" placeholder="Enter your name" name="name" value="{{ old('name') }}" required>
        </div>
        
        <div class="form-group mb-3">
          <label for="email" class="form-label">Email:</label>
          <small class="form-text text-muted">We'll never share your email.</small>
          <input type="email" class="form-control" id="email" placeholder="Enter your email" name="email" value="{{ old('email') }}" required>
        </div>
        
        <div class="form-group mb-3">
          <label for="password" class="form-label">Password:</label>
          <input type="password" class="form-control" id="password" placeholder="Create password" name="password" required aria-describedby="passwordHelp">
          <small id="passwordHelp" class="form-text text-muted">At least 8 characters, with upper/lowercase, number, and symbol.</small>
        </div>
        
        <div class="form-group mb-2">
          <label for="password_confirmation" class="form-label">Password Confirmation:</label>
          <input type="password" class="form-control" id="password_confirmation" placeholder="Confirm password" name="password_confirmation" required>
          <small class="form-text text-muted">Re-enter your password.</small>
        </div>
        
        <div class="form-group mb-2">
    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
</div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
      @if ($errors->has('password'))
        <div class="alert alert-danger mt-2">
          {{ $errors->first('password') }}
        </div>
      @endif
      @if (session('password_strength'))
        <div class="alert alert-warning mt-2">
          {{ session('password_strength') }}
        </div>
      @endif
      <div class="alert alert-info mt-2">
        <ul class="mb-0">
          <li>Password must be at least 8 characters long</li>
          <li>Include uppercase and lowercase letters</li>
          <li>Include at least one number</li>
          <li>Include at least one special character</li>
        </ul>
      </div>

    </div>
  </div>
</div>
@endsection
