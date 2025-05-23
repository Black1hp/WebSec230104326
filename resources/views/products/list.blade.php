@extends('layouts.master')
@section('title', 'Test Page')
@section('content')
<div class="row mt-2">
    <div class="col col-10">
        <h1>Products</h1>
    </div>
    <div class="col col-2">
        @can('add_products')
        <a href="{{route('products_edit')}}" class="btn btn-success form-control">Add Product</a>
        @endcan
    </div>
</div>

<form>
    <div class="row">
        <div class="col col-sm-2">
            <input name="keywords" type="text"  class="form-control" placeholder="Search Keywords" value="{{ request()->keywords }}" />
        </div>
        <div class="col col-sm-2">
            <input name="min_price" type="numeric"  class="form-control" placeholder="Min Price" value="{{ request()->min_price }}"/>
        </div>
        <div class="col col-sm-2">
            <input name="max_price" type="numeric"  class="form-control" placeholder="Max Price" value="{{ request()->max_price }}"/>
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
            <a href="{{ route('products_list') }}" class="btn btn-danger">Reset</a>
        </div>
    </div>
</form>

{{--@if(!empty(request()->keywords))--}}
{{--    <div class="card mt-2">--}}
{{--        <div class="card-body">--}}
{{--            view search results: <span> {{request()->keywords}}</span>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endif--}}


@foreach($products as $product)
    <div class="card mt-2">
        <div class="card-body">
            <div class="row">
                <div class="col col-sm-12 col-lg-4">
                    <img src="{{asset("images/$product->photo")}}" class="img-thumbnail" alt="{{$product->name}}" width="100%">
                </div>
                <div class="col col-sm-12 col-lg-8 mt-3">
                    <div class="row mb-2">
					    <div class="col-8">
					        <h3>{{$product->name}}</h3>
					    </div>
					    <div class="col col-2">
                            @can('edit_products')
					        <a href="{{route('products_edit', $product->id)}}" class="btn btn-success form-control">Edit</a>
                            @endcan
					    </div>
					    <div class="col col-2">
                            @can('delete_products')
					        <a href="{{route('products_delete', $product->id)}}" class="btn btn-danger form-control">Delete</a>
                            @endcan
					    </div>
					</div>

                    <table class="table table-striped">
                        <tr><th width="20%">Name</th><td>{{$product->name}}</td></tr>
                        <tr><th>Model</th><td>{{$product->model}}</td></tr>
                        <tr><th>Code</th><td>{{$product->code}}</td></tr>
                        <tr><th>Price</th><td>{{$product->price}}</td></tr>
                        <tr><th>Amount in Stock</th><td>
                            @if($product->amount > 0)
                                <span class="badge bg-success">{{$product->amount}} available</span>
                            @else
                                <span class="badge bg-danger">Out of stock</span>
                            @endif
                        </td></tr>
                        <tr><th>Description</th><td>{{$product->description}}</td></tr>
                        <tr>
                            <th>Likes</th>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-danger me-2">
                                        <i class="bi bi-heart-fill"></i> {{ $product->likes_count }}
                                    </span>
                                    @auth
                                        @role('Customer')
                                            @if($product->isLikedByUser())
                                                <form action="{{ route('product_toggle_like', ['product' => $product->id]) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-heart-fill"></i> Liked
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('product_toggle_like', ['product' => $product->id]) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-heart"></i> Like
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            @if($product->isLikedByUser())
                                                <span class="text-success small">
                                                    <i class="bi bi-check-circle-fill"></i> You liked this
                                                </span>
                                            @endif
                                        @endrole
                                    @endauth
                                </div>
                            </td>
                        </tr>
                    </table>

                    @role('Customer')
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <form action="{{ route('products_purchase', $product->id) }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <input type="number" name="quantity" class="form-control" value="1" min="1" max="{{ $product->amount }}" {{ $product->amount <= 0 ? 'disabled' : '' }}>
                                    <button type="submit" class="btn btn-primary" {{ $product->amount <= 0 ? 'disabled' : '' }}>
                                        {{ $product->amount > 0 ? 'Purchase' : 'Out of Stock' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-0">Your Credit: <span class="badge bg-success">{{ auth()->user() ? auth()->user()->credit : 0 }}</span></p>
                        </div>
                    </div>
                    @endrole
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
