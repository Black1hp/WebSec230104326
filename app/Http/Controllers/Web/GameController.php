<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Game::class);
        $games = Game::all();
        return view('games.index', compact('games'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Game::class);
        return view('games.edit');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Game $game = null)
    {
        // If no $game is provided (new game), create an empty model instance.
        $game = $game ?? new Game();
        
        if ($game->exists) {
            $this->authorize('update', $game);
        } else {
            $this->authorize('create', Game::class);
        }
        
        return view('games.edit', compact('game'));
    }

    /**
     * Store a newly created resource or update an existing one.
     */
    public function save(Request $request, Game $game = null)
    {
        // If no $game is provided, create a new instance.
        $isNew = !($game && $game->exists);
        $game = $game ?? new Game();
        
        if ($isNew) {
            $this->authorize('create', Game::class);
        } else {
            $this->authorize('update', $game);
        }

        // Validate the request data
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'platform' => ['required', 'string'],
            'user_id' => ['sometimes', 'exists:users,id'],
        ]);

        // Set user_id if not provided
        if (!isset($validated['user_id'])) {
            $validated['user_id'] = auth()->id();
        }

        // Fill model with validated data
        $game->fill($validated);
        $game->save();

        // Redirect to the games index page
        return redirect()->route('games.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        $this->authorize('update', $game);
        $game->update($request->all());
        return redirect()->route('games.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Game $game)
    {
        $this->authorize('delete', $game);
        $game->delete();
        return redirect()->route('games.index');
    }
}
