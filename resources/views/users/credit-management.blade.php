@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Credit Management</h1>
        <div>
            <a href="{{ auth()->user()->role === 'admin' ? route('users.index') : route('employee.customers') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Customer Credit Balances</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Current Credit</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($user->role) {
                                            'customer' => 'bg-info text-dark',
                                            'user' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success fs-6">${{ number_format($user->credit, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addCreditModal{{ $user->id }}">
                                        Add Credit
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateCreditModal{{ $user->id }}">
                                            Update Balance
                                        </button>
                                    @endif
                                    <a href="{{ route('profile.user', $user) }}" class="btn btn-sm btn-info">
                                        View Profile
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Add Credit Modal -->
                            <div class="modal fade" id="addCreditModal{{ $user->id }}" tabindex="-1" aria-labelledby="addCreditModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addCreditModalLabel{{ $user->id }}">Add Credit for {{ $user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ auth()->user()->role === 'admin' ? route('admin.add-credit', $user) : route('employee.add-credit', $user) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="amount{{ $user->id }}" class="form-label">Amount to Add ($)</label>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           id="amount{{ $user->id }}" 
                                                           name="amount" 
                                                           @if(auth()->user()->role !== 'admin')
                                                           min="0.01"
                                                           step="0.01"
                                                           @else
                                                           step="any"
                                                           @endif
                                                           required>
                                                    <div class="form-text">Current balance: ${{ number_format($user->credit, 2) }}</div>
                                                    @if(auth()->user()->role !== 'admin')
                                                        <div class="form-text text-danger">
                                                            <strong>Note:</strong> You can only add positive credit amounts.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Add Credit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Update Credit Modal (Admin Only) -->
                            @if(auth()->user()->role === 'admin')
                                <div class="modal fade" id="updateCreditModal{{ $user->id }}" tabindex="-1" aria-labelledby="updateCreditModalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="updateCreditModalLabel{{ $user->id }}">Update Credit for {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.update-credit', $user) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="newAmount{{ $user->id }}" class="form-label">New Credit Balance ($)</label>
                                                        <input type="number" class="form-control" id="newAmount{{ $user->id }}" name="amount" min="0" step="0.01" value="{{ $user->credit }}" required>
                                                        <div class="form-text">Current balance: ${{ number_format($user->credit, 2) }}</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Balance</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection