@extends('layouts.app')
@section('title', isset($game->id) ? 'Edit Game' : 'Create Game')
@section('content')
    <div class="container">
        <h1>{{ isset($game->id) ? 'Edit Game' : 'Create Game' }}</h1>
        
        <form action="{{ isset($game->id) ? route('games.save', $game->id) : route('games.save') }}" method="post">
            @csrf

            <div class="row mb-3">
                <div class="col-12">
                    <label for="name" class="form-label">Game Name:</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           placeholder="Enter game name" name="name" required 
                           value="{{ old('name', $game->name ?? '') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <label for="description" class="form-label">Description:</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              placeholder="Enter game description" name="description" rows="4">{{ old('description', $game->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="platform" class="form-label">Platform:</label>
                    <select class="form-select @error('platform') is-invalid @enderror" name="platform">
                        <option value="">Select Platform</option>
                        <option value="PC" {{ (old('platform', $game->platform ?? '') == 'PC') ? 'selected' : '' }}>PC</option>
                        <option value="PlayStation" {{ (old('platform', $game->platform ?? '') == 'PlayStation') ? 'selected' : '' }}>PlayStation</option>
                        <option value="Xbox" {{ (old('platform', $game->platform ?? '') == 'Xbox') ? 'selected' : '' }}>Xbox</option>
                        <option value="Nintendo" {{ (old('platform', $game->platform ?? '') == 'Nintendo') ? 'selected' : '' }}>Nintendo</option>
                        <option value="Mobile" {{ (old('platform', $game->platform ?? '') == 'Mobile') ? 'selected' : '' }}>Mobile</option>
                    </select>
                    @error('platform')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="status" class="form-label">Status:</label>
                    <select class="form-select @error('status') is-invalid @enderror" name="status">
                        <option value="">Select Status</option>
                        <option value="Available" {{ (old('status', $game->status ?? '') == 'Available') ? 'selected' : '' }}>Available</option>
                        <option value="Coming Soon" {{ (old('status', $game->status ?? '') == 'Coming Soon') ? 'selected' : '' }}>Coming Soon</option>
                        <option value="Sold Out" {{ (old('status', $game->status ?? '') == 'Sold Out') ? 'selected' : '' }}>Sold Out</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Game</button>
                    <a href="{{ route('games.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection
