@extends('layouts.master')
@section('title', 'User Purchases')
@section('content')
<div class="row mt-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Purchase History for {{ $user->name }}</h1>
            <div>
                <p class="mb-0">User Credit: <span class="badge bg-success">{{ $user->credit }}</span></p>
                <a href="{{ route('users') }}" class="btn btn-primary mt-2">Back to Users</a>
            </div>
        </div>

        @if($purchases->isEmpty())
            <div class="alert alert-info">
                <p class="mb-0">This user hasn't made any purchases yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Product Code</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->id }}</td>
                                <td>{{ $purchase->product_name }}</td>
                                <td>{{ $purchase->product_code }}</td>
                                <td>{{ $purchase->product_price }}</td>
                                <td>{{ $purchase->quantity }}</td>
                                <td>{{ $purchase->total_price }}</td>
                                <td>{{ date('Y-m-d H:i', strtotime($purchase->created_at)) }}</td>
                                <td>
                                    <span class="badge {{ $purchase->status == 'completed' ? 'bg-success' : ($purchase->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Purchase Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="fw-bold">Total Purchases:</p>
                            <h3>{{ $purchases->count() }}</h3>
                        </div>
                        <div class="col-md-4">
                            <p class="fw-bold">Total Items:</p>
                            <h3>{{ $purchases->sum('quantity') }}</h3>
                        </div>
                        <div class="col-md-4">
                            <p class="fw-bold">Total Spent:</p>
                            <h3>{{ $purchases->sum('total_price') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection 