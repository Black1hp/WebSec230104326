@extends('layouts.app')
@section('title', 'Edit Page')
@section('content')

    <form action="{{ $product->id ? route('products.save', $product->id) : route('products.save') }}" method="post">
        {{ csrf_field() }}

        <div class="row mb-2">
            <div class="col-6">
                <label for="code" class="form-label">Code:</label>
                <input type="text" class="form-control @error('code') is-invalid @enderror"
                       placeholder="Code" name="code" required value="{{ old('code', $product->code) }}">
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6">
                <label for="model" class="form-label">Model:</label>
                <input type="text" class="form-control @error('model') is-invalid @enderror"
                       placeholder="Model" name="model" required value="{{ old('model', $product->model) }}">
                @error('model')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
                <label for="name" class="form-label">Name:</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       placeholder="Name" name="name" required value="{{ old('name', $product->name) }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-6">
                <label for="price" class="form-label">Price:</label>
                <input type="numeric" class="form-control @error('price') is-invalid @enderror"
                       placeholder="Price" name="price" required value="{{ old('price', $product->price) }}">
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6">
                <label for="stock" class="form-label">Stock:</label>
                <input type="number" class="form-control @error('stock') is-invalid @enderror"
                       placeholder="Stock" name="stock" required value="{{ old('stock', $product->stock) }}">
                @error('stock')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        <div class="row mb-2">
            <div class="col-6">
                <label for="photo" class="form-label">Photo:</label>
                <input type="text" class="form-control @error('photo') is-invalid @enderror"
                       placeholder="Photo" name="photo" required value="{{ old('photo', $product->photo) }}">
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
                <label for="description" class="form-label">Description:</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          placeholder="Description" name="description" required>{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
