@extends('layouts.app')
@section('title', 'Game Details')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>{{ $game->name }}</h1>
            </div>
            <div class="col-md-4 text-end">
                @can('update', $game)
                <a href="{{ route('games.edit', $game->id) }}" class="btn btn-primary">Edit Game</a>
                @endcan
                @can('delete', $game)
                <form action="{{ route('games.delete', $game->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this game?')">Delete</button>
                </form>
                @endcan
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <strong>Game Information</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Platform:</strong> {{ $game->platform }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> {{ $game->status }}</p>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h4>Description</h4>
                        <p>{{ $game->description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <a href="{{ route('games.index') }}" class="btn btn-secondary">Back to Games</a>
            </div>
        </div>
    </div>
@endsection 