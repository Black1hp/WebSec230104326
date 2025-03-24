@extends('layouts.app')
@section('title', 'Prime Numbers')
@section('content')
    <div class="row">
        <div class="col col-10">
            <h1>Games</h1>
        </div>
        @can('create', App\Models\Game::class)
            <div class="col col-2">
                <a href="{{ route('games.create') }}" class="btn btn-success form-control">Add Game</a>
            </div>
        @endcan
    </div>
    @foreach($games as $game)
        <div class="card mt-2">
            <div class="card-body">
                <div class="row">
                    <div class="col col-sm-12 col-lg-4">
                        <img src="{{asset("images/$game->photo")}}"
                             class="img-thumbnail" alt="{{$game->name}}" width="100%">
                    </div>
                    <div class="col col-sm-12 col-lg-8 mt-3">
                        <div class="row mb-2">
                            <div class="col-8">
                                <h3>{{$game->name}}</h3>
                            </div>
                            @can('update', $game)
                                <div class="col col-2">
                                    <a href="{{route('games.edit', $game->id)}}"
                                       class="btn btn-success form-control">Edit</a>
                                </div>
                            @endcan
                            @can('delete', $game)
                                <div class="col col-2">
                                    <form action="{{ route('games.delete', $game->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger form-control">Delete</button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                        <table class="table table-striped table-bordered table-hover">
                            <tr><th width="20%">Name</th><td>{{$game->name}}</td></tr>
                            <tr><th>Description</th><td>{{$game->description}}</td></tr>
                            <tr><th>Plarform</th><td>{{$game->platform}}</td></tr>
                            <tr><th>Price</th><td>{{$game->price}}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
