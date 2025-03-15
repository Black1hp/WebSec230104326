<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UsersController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
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
        return view('users.edit', compact('user'));
    }

    /**
     * Store or update the specified user in storage.
     */
    public function save(Request $request, User $user = null)
    {
        // If no $user is provided, create a new instance.
        $user = $user ?? new User();

        // Fill the model with validated form data.
        // Make sure $fillable in App\Models\User matches these fields.
        $user->fill($request->all());
        $user->save();

        // Redirect to the users index page (adjust the route name as needed).
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified user from storage.
     */
    public function delete(Request $request, User $user)
    {
        $user->delete();
        return redirect()->route('users.index');
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
            'email' => 'The provided credentials do not match our records.',
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
        ]);

        // Assign the 'user' role to new registrations
        $user->assignRole('user');

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
        return view('users.profile', ['user' => Auth::user()]);
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
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
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password updated successfully!');
    }
}
