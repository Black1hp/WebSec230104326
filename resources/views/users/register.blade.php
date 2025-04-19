@extends('layouts.master')
@section('title', 'Register')
@section('content')
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
          <input type="text" class="form-control" id="name" placeholder="Enter your name" name="name" value="{{ old('name') }}" required>
        </div>
        
        <div class="form-group mb-3">
          <label for="email" class="form-label">Email:</label>
          <input type="email" class="form-control" id="email" placeholder="Enter your email" name="email" value="{{ old('email') }}" required>
        </div>
        
        <div class="form-group mb-3">
          <label for="password" class="form-label">Password:</label>
          <input type="password" class="form-control" id="password" placeholder="Create password" name="password" required>
        </div>
        
        <div class="form-group mb-3">
          <label for="password_confirmation" class="form-label">Password Confirmation:</label>
          <input type="password" class="form-control" id="password_confirmation" placeholder="Confirm password" name="password_confirmation" required>
        </div>
        
        <div class="form-group mb-4">
          <div class="cf-turnstile" data-sitekey="0x4AAAAAABPbY4booday-3at" data-theme="light"></div>
        </div>
        
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
