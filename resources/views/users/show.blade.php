@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>User Profile</h1>
                <div>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
                    @elseif(auth()->user()->role === 'employee')
                        <a href="{{ route('employee.customers') }}" class="btn btn-secondary">Back to Customers</a>
                    @endif
                </div>
            </div>

            <!-- User Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $user->name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Role:</strong>
                                <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : ($user->role === 'employee' ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </p>
                            @if($user->role === 'customer')
                                <p>
                                    <strong>Credit Balance:</strong>
                                    <span class="badge bg-success">${{ number_format($user->credit, 2) }}</span>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase History Card -->
            @if($user->role === 'customer')
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Purchase History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price Paid</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->purchases()->with('product')->latest()->get() as $purchase)
                                        <tr>
                                            <td>{{ $purchase->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('products.show', $purchase->product) }}" class="text-decoration-none">
                                                    {{ $purchase->product->name }}
                                                </a>
                                            </td>
                                            <td>{{ $purchase->quantity }}</td>
                                            <td>${{ number_format($purchase->price_paid, 2) }}</td>
                                            <td>${{ number_format($purchase->quantity * $purchase->price_paid, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No purchase history found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
