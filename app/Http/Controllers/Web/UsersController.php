<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use function Laravel\Prompts\alert;

class UsersController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $users = User::all();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating/editing a user.
     */
    public function edit(Request $request, User $user = null)
    {
        // If no $user is provided (new user), create an empty model instance.
        $user = $user ?? new User();

        if ($user->exists) {
            $this->authorize('update', $user);
        } else {
            $this->authorize('create', User::class);
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Store or update the specified user in storage.
     */
    public function save(Request $request, User $user = null)
    {
        // If no $user is provided, create a new instance.
        $isNew = !($user && $user->exists);
        $user = $user ?? new User();

        if ($isNew) {
            $this->authorize('create', User::class);
        } else {
            $this->authorize('update', $user);
        }

        // Fill the model with validated form data.
        // Make sure $fillable in App\Models\User matches these fields.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . ($user->id ?? 'null')],
            'role' => ['required', 'string', 'in:admin,user'], // Added validation for 'role'
        ]);

        $user->fill($validated);
        $user->save();

        // Redirect to the users index page (adjust the route name as needed).
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified user from storage.
     */
    public function delete(Request $request, User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return redirect()->route('users.index');
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $this->authorize('view', $user);
            return view('users.show', compact('user'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        } catch (\Exception $e) {
            Log::error('Error showing user: ' . $e->getMessage());
            return redirect()->route('users.index')
                ->with('error', 'An error occurred while trying to view this user.');
        }
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('users.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => __('The provided credentials do not match our records.'),
        ])->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('users.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user'  // Set default role
        ]);

        Auth::login($user);

        return redirect('/');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Show the user's profile page.
     */
    public function profile()
    {
        $user = Auth::user();
        $this->authorize('view', $user);
        return view('users.profile', ['user' => $user]);
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('status', 'Profile updated successfully!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password updated successfully!');
    }
}
