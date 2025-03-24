@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Purchases</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Account Credit</h5>
                    <p class="card-text">Your current credit: ${{ number_format(auth()->user()->credit, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($purchases->isEmpty())
        <div class="alert alert-info">
            You haven't made any purchases yet.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price Paid</th>
                        <th>Total</th>
                        <th>Purchase Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->product->name }}</td>
                            <td>{{ $purchase->quantity }}</td>
                            <td>${{ number_format($purchase->price_paid, 2) }}</td>
                            <td>${{ number_format($purchase->quantity * $purchase->price_paid, 2) }}</td>
                            <td>{{ $purchase->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection 