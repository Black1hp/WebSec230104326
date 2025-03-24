@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <img src="{{ asset("images/$product->photo") }}" class="img-fluid" alt="{{ $product->name }}">
        </div>
        <div class="col-md-6">
            <h2>{{ $product->name }}</h2>
            <p class="text-muted">Model: {{ $product->model }}</p>
            <p class="text-muted">Code: {{ $product->code }}</p>
            
            <div class="mb-4">
                <h4>Description</h4>
                <p>{{ $product->description }}</p>
            </div>

            <div class="mb-4">
                <h4>Price</h4>
                <p class="h3">${{ number_format($product->price, 2) }}</p>
            </div>

            <div class="mb-4">
                <h4>Stock</h4>
                <p>{{ $product->stock }} units available</p>
            </div>

            @if(auth()->check() && auth()->user()->isCustomer())
                @if($product->isInStock())
                    <form action="{{ route('purchases.store', $product) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Purchase</button>
                    </form>
                @else
                    <div class="alert alert-warning">
                        This product is currently out of stock.
                    </div>
                @endif
            @endif

            @if(auth()->check() && (auth()->user()->isEmployee() || auth()->user()->isAdmin()))
                <div class="mt-4">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-success">Edit Product</a>
                    <form action="{{ route('products.delete', $product) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete Product</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 