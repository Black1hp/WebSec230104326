@extends('layouts.master')
@section('title', 'User Profile')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <!-- Avatar -->
                        <div class="me-3">
                            @if($user->avatar)
                                <img src="{{ asset('storage/avatars/'.$user->avatar) }}" alt="Avatar" class="rounded-circle" width="80" height="80">
                            @else
                                <span class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2.5rem;color:#fff;">
                                    <i class="bi bi-person-circle"></i>
                                </span>
                            @endif
                        </div>
                        <div>
                            <h2 class="mb-1">{{$user->name}}</h2>
                            <div class="text-muted"><i class="bi bi-envelope"></i> {{$user->email}}</div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-calendar"></i> Joined: {{$user->created_at->format('F j, Y')}}
                                @if($user->updated_at && $user->updated_at != $user->created_at)
                                    <span class="ms-2"><i class="bi bi-clock-history"></i> Updated: {{$user->updated_at->diffForHumans()}}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <strong>Roles:</strong>
                        @foreach($user->roles as $role)
                            @if($role->name == 'Admin')
                            <span class="badge bg-warning text-dark"><i class="bi bi-shield-fill-check"></i> {{$role->name}}</span>
                            @elseif($role->name == 'Employee')
                            <span class="badge bg-info"><i class="bi bi-person-badge-fill"></i> {{$role->name}}</span>
                            @elseif($role->name == 'Customer')
                            <span class="badge bg-success"><i class="bi bi-person-fill"></i> {{$role->name}}</span>
                            @else
                            <span class="badge bg-primary"><i class="bi bi-person-badge"></i> {{$role->name}}</span>
                            @endif
                        @endforeach
                    </div>
                    @if($user->hasRole('Customer'))
                    <div class="mb-3">
                        <strong>Credit Balance:</strong>
                        <span class="badge bg-success"><i class="bi bi-cash"></i> {{$user->credit}}</span>
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Permissions:</strong>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            @foreach($permissions as $permission)
                                <span class="badge bg-secondary"><i class="bi bi-shield-lock"></i> {{$permission->display_name}}</span>
                            @endforeach
                            @if(count($permissions) == 0)
                                <span class="text-muted">No specific permissions assigned.</span>
                            @endif
                        </div>
                    </div>
                    <!-- Recent Activity Placeholder -->
                    <div class="mb-3">
                        <strong>Recent Activity:</strong>
                        <span class="text-muted">No recent activity to display.</span>
                    </div>
                    <hr>
                    <div class="d-flex flex-wrap gap-2">
                        @if($user->hasRole('Customer') && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Employee')))
                            <a class="btn btn-success" href='{{route('charge_credit', $user->id)}}'><i class="bi bi-plus-circle"></i> Charge Credit</a>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin_users')||auth()->id()==$user->id)
                            <a class="btn btn-primary" href='{{route('edit_password', $user->id)}}'><i class="bi bi-key"></i> Change Password</a>
                        @endif
                        @if(auth()->user()->hasPermissionTo('edit_users')||auth()->id()==$user->id)
                            <a href="{{route('users_edit', $user->id)}}" class="btn btn-outline-secondary"><i class="bi bi-pencil-square"></i> Edit Profile</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap Icons CDN (if not already included) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endsection
