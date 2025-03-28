@extends('layouts.app')
@section('title', 'Prime Numbers')
@section('content')
    <div class="row">
        <div class="col col-10">
            <h1>Products</h1>
        </div>
        @can('create', App\Models\Product::class)
            <div class="col col-2">
                <a href="{{route('products.edit')}}"
                   class="btn btn-success form-control">Add Product</a>
            </div>
        @endcan
    </div>
    <form>
        <div class="row mt-4" >
            <div class="col col-sm-2">
                <input name="keywords" type="text" class="form-control" placeholder="Search Keywords" value="{{ request()->keywords }}" />
            </div>
            <div class="col col-sm-2">
                <input name="min_price" type="numeric" class="form-control" placeholder="Min Price" value="{{ request()->min_price }}"/>
            </div>
            <div class="col col-sm-2">
                <input name="max_price" type="numeric" class="form-control" placeholder="Max Price" value="{{ request()->max_price }}"/>
            </div>
            <div class="col col-sm-2">
                <select name="order_by" class="form-select">
                    <option value="" {{ request()->order_by==""?"selected":"" }} disabled>Order By</option>
                    <option value="name" {{ request()->order_by=="name"?"selected":"" }}>Name</option>
                    <option value="price" {{ request()->order_by=="price"?"selected":"" }}>Price</option>
                </select>
            </div>
            <div class="col col-sm-2">
                <select name="order_direction" class="form-select">
                    <option value="" {{ request()->order_direction==""?"selected":"" }} disabled>Order Direction</option>
                    <option value="ASC" {{ request()->order_direction=="ASC"?"selected":"" }}>ASC</option>
                    <option value="DESC" {{ request()->order_direction=="DESC"?"selected":"" }}>DESC</option>
                </select>
            </div>
            <div class="col col-sm-1">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            <div class="col col-sm-1">
                <button type="reset" class="btn btn-danger">Reset</button>
            </div>
        </div>
    </form>
    @foreach($products as $product)
        <div class="card mt-2 {{ $product->stock <= 0 ? 'border-danger' : '' }}">
            <div class="card-body {{ $product->stock <= 0 ? 'bg-danger bg-opacity-10' : '' }}">
                <div class="row">
                    <div class="col col-sm-12 col-lg-4">
                        <img src="{{asset("images/$product->photo")}}"
                             class="img-thumbnail" alt="{{$product->name}}" width="100%">
                    </div>
                    <div class="col col-sm-12 col-lg-8 mt-3">
                        <div class="row mb-2">
                            <div class="col-8">
                                <h3>
                                    {{$product->name}}
                                    @if($product->stock <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                </h3>
                                <h4 class="text-primary">${{ number_format($product->price, 2) }}</h4>
                            </div>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'employee')
                                <div class="col col-2">
                                    <a href="{{route('products.edit', $product->id)}}"
                                       class="btn btn-success form-control">Edit</a>
                                </div>
                                <div class="col col-2">
                                    <a href="{{route('products.delete', $product->id)}}"
                                       class="btn btn-danger form-control">Delete</a>
                                </div>
                            @elseif((auth()->user()->role === 'customer' || auth()->user()->role === 'user') && $product->stock > 0)
                                <div class="col col-4">
                                    <button type="button" class="btn btn-primary form-control" data-bs-toggle="modal" data-bs-target="#purchaseModal{{ $product->id }}">
                                        Purchase
                                    </button>
                                </div>
                            @endif
                        </div>

                        <table class="table table-striped">
                            <tr><th width="20%">Name</th><td>{{$product->name}}</td></tr>
                            <tr><th>Model</th><td>{{$product->model}}</td></tr>
                            <tr><th>Code</th><td>{{$product->code}}</td></tr>
                            <tr><th>Price</th><td>${{ number_format($product->price, 2) }}</td></tr>
                            <tr><th>Stock</th>
                                <td>
                                    @if($product->stock > 0)
                                        <span class="badge bg-success">{{$product->stock}} in stock</span>
                                    @else
                                        <span class="badge bg-danger">Out of stock</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><th>Description</th><td>{{$product->description}}</td></tr>
                        </table>
                        
                        @if((auth()->user()->role === 'customer' || auth()->user()->role === 'user') && $product->stock > 0)
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Your Credit:</strong> ${{ number_format(auth()->user()->credit, 2) }}
                                            @if(auth()->user()->credit < $product->price)
                                                <span class="text-danger ms-2">Insufficient credit to purchase</span>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#purchaseModal{{ $product->id }}" 
                                                {{ auth()->user()->credit < $product->price ? 'disabled' : '' }}>
                                            Purchase
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Purchase Modal -->
                            <div class="modal fade" id="purchaseModal{{ $product->id }}" tabindex="-1" aria-labelledby="purchaseModalLabel{{ $product->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="purchaseModalLabel{{ $product->id }}">Purchase {{ $product->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('purchases.store', $product) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="quantity{{ $product->id }}" class="form-label">Quantity</label>
                                                    <input type="number" class="form-control" id="quantity{{ $product->id }}" name="quantity" 
                                                           min="1" max="{{ $product->stock }}" value="1" required>
                                                </div>
                                                <div class="mb-3">
                                                    <p><strong>Price per unit:</strong> ${{ number_format($product->price, 2) }}</p>
                                                    <p><strong>Your credit:</strong> ${{ number_format(auth()->user()->credit, 2) }}</p>
                                                    <p><strong>Maximum quantity you can purchase:</strong> 
                                                        {{ floor(min(auth()->user()->credit / $product->price, $product->stock)) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary" 
                                                        {{ auth()->user()->credit < $product->price ? 'disabled' : '' }}>
                                                    Confirm Purchase
                                                </button>
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
    @endforeach
@endsection
