<<<<<<< HEAD
@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">{{ isset($user->id) ? 'Edit User' : 'Create New User' }}</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ isset($user->id) ? route('users.save', $user->id) : route('users.save') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if (!isset($user->id))
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" required>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select @error('role') is-invalid @enderror" name="role" id="role">
                                    <option value="user" {{ (old('role', $user->role ?? 'user') == 'user') ? 'selected' : '' }}>User</option>
                                    <option value="admin" {{ (old('role', $user->role ?? '') == 'admin') ? 'selected' : '' }}>Admin</option>
                                </select>
                                @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($user->id) ? 'Update User' : 'Create User' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
=======
@extends('layouts.master')
@section('title', 'Edit User')
@section('content')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
  $("#clean_permissions").click(function(){
    $('#permissions').val([]);
  });
  $("#clean_roles").click(function(){
    $('#roles').val([]);
  });
});
</script>
<div class="d-flex justify-content-center">
    <div class="row m-4 col-sm-8">
        <form action="{{route('users_save', $user->id)}}" method="post">
            {{ csrf_field() }}
            @foreach($errors->all() as $error)
            <div class="alert alert-danger">
            <strong>Error!</strong> {{$error}}
            </div>
            @endforeach
            <div class="row mb-2">
                <div class="col-12">
                    <label for="code" class="form-label">Name:</label>
                    <input type="text" class="form-control" placeholder="Name" name="name" required value="{{$user->name}}">
                </div>
            </div>
            @can('admin_users')
            <div class="col-12 mb-2">
                <label for="model" class="form-label">Roles:</label> (<a href='#' id='clean_roles'>reset</a>)
                <select multiple class="form-select" id='roles' name="roles[]">
                    @foreach($roles as $role)
                    <option value='{{$role->name}}' {{$role->taken?'selected':''}}>
                        {{$role->name}}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-2">
                <label for="model" class="form-label">Direct Permissions:</label> (<a href='#' id='clean_permissions'>reset</a>)
                <select multiple class="form-select" id='permissions' name="permissions[]">
                @foreach($permissions as $permission)
                    <option value='{{$permission->name}}' {{$permission->taken?'selected':''}}>
                        {{$permission->display_name}}
                    </option>
                    @endforeach
                </select>
            </div>
            @endcan

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
>>>>>>> Midterm-v2
@endsection
