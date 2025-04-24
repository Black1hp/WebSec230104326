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
          <label for="name" class="form-label">Name:
            <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Enter your full name."></i>
          </label>
          <input type="text" class="form-control" id="name" placeholder="Enter your name" name="name" value="{{ old('name') }}" required>
        </div>
        
        <div class="form-group mb-3">
          <label for="email" class="form-label">Email:
            <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="We'll never share your email."></i>
          </label>
          <input type="email" class="form-control" id="email" placeholder="Enter your email" name="email" value="{{ old('email') }}" required>
        </div>
        
        <div class="form-group mb-3">
          <label for="password" class="form-label">Password:
            <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="At least 8 characters, with upper/lowercase, number, and symbol."></i>
          </label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" placeholder="Create password" name="password" required aria-describedby="passwordHelp">
            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1"><i class="bi bi-eye"></i></button>
          </div>
          <div class="progress mt-2" style="height: 5px;">
            <div id="passwordStrength" class="progress-bar bg-danger" style="width: 0%;"></div>
          </div>
          <small id="passwordHelp" class="form-text text-muted">Use a strong password.</small>
        </div>
        
        <div class="form-group mb-2">
          <label for="password_confirmation" class="form-label">Password Confirmation:
            <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Re-enter your password."></i>
          </label>
          <div class="input-group">
            <input type="password" class="form-control" id="password_confirmation" placeholder="Confirm password" name="password_confirmation" required>
            <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirm" tabindex="-1"><i class="bi bi-eye"></i></button>
          </div>
        </div>
        
        <div class="form-group mb-2">
    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
</div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
      <!-- Password strength, show/hide, and tooltips scripts -->

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          // Enable Bootstrap tooltips
          var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
          });
          // Show/hide password
          document.getElementById('togglePassword').onclick = function() {
            const pwd = document.getElementById('password');
            const icon = this.querySelector('i');
            if (pwd.type === 'password') {
              pwd.type = 'text'; icon.classList.remove('bi-eye'); icon.classList.add('bi-eye-slash');
            } else {
              pwd.type = 'password'; icon.classList.remove('bi-eye-slash'); icon.classList.add('bi-eye');
            }
          };
          document.getElementById('togglePasswordConfirm').onclick = function() {
            const pwd = document.getElementById('password_confirmation');
            const icon = this.querySelector('i');
            if (pwd.type === 'password') {
              pwd.type = 'text'; icon.classList.remove('bi-eye'); icon.classList.add('bi-eye-slash');
            } else {
              pwd.type = 'password'; icon.classList.remove('bi-eye-slash'); icon.classList.add('bi-eye');
            }
          };
          // Password strength meter
          const password = document.getElementById('password');
          const strengthBar = document.getElementById('passwordStrength');
          const helpText = document.getElementById('passwordHelp');
          password.addEventListener('input', function() {
            const val = password.value;
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            let percent = [0,20,40,60,80,100][score];
            let color = ['bg-danger','bg-danger','bg-warning','bg-info','bg-primary','bg-success'][score];
            strengthBar.style.width = percent + '%';
            strengthBar.className = 'progress-bar ' + color;
            helpText.textContent = percent < 100 ? 'Password could be stronger.' : 'Strong password!';
          });
        });
      </script>
    </div>
  </div>
</div>
@endsection
