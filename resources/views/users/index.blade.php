@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Users Management</h1>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add New User
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Credit</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($user->role) {
                                            'admin' => 'bg-danger',
                                            'employee' => 'bg-warning text-dark',
                                            'customer' => 'bg-info text-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->role === 'customer' || $user->role === 'user')
                                        ${{ number_format($user->credit, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @if(($user->role === 'customer' || $user->role === 'user') && auth()->user()->role === 'admin')
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addCreditModal{{ $user->id }}">
                                            Add Credit
                                        </button>
                                    @endif
                                    <form action="{{ route('users.delete', $user) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            
                            <!-- Add Credit Modal -->
                            @if(($user->role === 'customer' || $user->role === 'user') && auth()->user()->role === 'admin')
                                <div class="modal fade" id="addCreditModal{{ $user->id }}" tabindex="-1" aria-labelledby="addCreditModalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="addCreditModalLabel{{ $user->id }}">Add Credit for {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.add-credit', $user) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="amount{{ $user->id }}" class="form-label">Amount ($)</label>
                                                        <input type="number" class="form-control" id="amount{{ $user->id }}" name="amount" min="0.01" step="0.01" required>
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
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No users found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
