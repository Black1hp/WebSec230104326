@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('User Details') }}</span>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">Back to Users</a>
                    @elseif(auth()->user()->role === 'employee')
                        <a href="{{ route('employee.customers') }}" class="btn btn-sm btn-secondary">Back to Customers</a>
                    @else
                        <a href="{{ route('profile') }}" class="btn btn-sm btn-secondary">Back to My Profile</a>
                    @endif
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">{{ __('User ID') }}</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $user->id }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $user->name }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">{{ __('Email') }}</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">{{ __('Role') }}</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">
                                @php
                                    $badgeClass = match($user->role) {
                                        'admin' => 'bg-danger',
                                        'employee' => 'bg-warning text-dark',
                                        'customer' => 'bg-info text-dark',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($user->role) }}</span>
                            </p>
                        </div>
                    </div>

                    @if($user->role === 'customer')
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">{{ __('Credit') }}</label>
                            <div class="col-md-6">
                                <p class="form-control-plaintext">${{ number_format($user->credit, 2) }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">{{ __('Created At') }}</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $user->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if(auth()->user()->role === 'admin')
                        <div class="row">
                            <div class="col-md-6 offset-md-4">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary me-2">Edit User</a>
                                <form action="{{ route('users.delete', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete User</button>
                                </form>
                            </div>
                        </div>
                    @endif
                    
                    @if(auth()->user()->role === 'employee' && $user->role === 'customer')
                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-4">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCreditModal">
                                    Add Credit
                                </button>
                            </div>
                        </div>
                        
                        <!-- Add Credit Modal -->
                        <div class="modal fade" id="addCreditModal" tabindex="-1" aria-labelledby="addCreditModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addCreditModalLabel">Add Credit for {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('employee.add-credit', $user) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="amount" class="form-label">Amount ($)</label>
                                                <input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Add Credit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
