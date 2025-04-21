<?php
namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gift;

class UsersController extends Controller {

	use ValidatesRequests;

    public function list(Request $request) {
        if(!auth()->user()->hasPermissionTo('show_users'))abort(401);

        $query = User::select('*');

        // Always exclude the current user from the list
        $query->where('id', '!=', auth()->id());

        // If the current user is an employee
        if(auth()->user()->hasRole('Employee')) {
            // Get admin role ID
            $adminRoleId = Role::where('name', 'Admin')->first()->id;
            // Get employee role ID
            $employeeRoleId = Role::where('name', 'Employee')->first()->id;

            // Get admin user IDs
            $adminUserIds = DB::table('model_has_roles')
                            ->where('role_id', $adminRoleId)
                            ->where('model_type', 'App\\Models\\User')
                            ->pluck('model_id')
                            ->toArray();

            // Get other employee IDs (excluding the current user)
            $otherEmployeeIds = DB::table('model_has_roles')
                                ->where('role_id', $employeeRoleId)
                                ->where('model_type', 'App\\Models\\User')
                                ->where('model_id', '!=', auth()->id())
                                ->pluck('model_id')
                                ->toArray();

            // Combine the IDs to exclude
            $excludeIds = array_merge($adminUserIds, $otherEmployeeIds);

            $query->whereNotIn('id', $excludeIds);
        }

        $query->when($request->keywords,
        fn($q)=> $q->where("name", "like", "%$request->keywords%"));

        // Get users with roles preloaded
        $users = $query->with('roles')->get();

        return view('users.list', compact('users'));
    }

	public function register(Request $request) {
        return view('users.register');
    }

    public function doRegister(Request $request) {
        // Cloudflare Turnstile CAPTCHA validation
        $turnstileResponse = $request->input('cf-turnstile-response');
        if (!$turnstileResponse) {
            return redirect()->back()->withInput($request->input())->withErrors(['captcha' => 'Please complete the CAPTCHA.']);
        }
        $cfSecret = env('CF_TURNSTILE_SECRET');
        $verifyResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $cfSecret,
            'response' => $turnstileResponse,
            'remoteip' => $request->ip(),
        ]);
        $verifyBody = $verifyResponse->json();
        if (!($verifyBody['success'] ?? false)) {
            return redirect()->back()->withInput($request->input())->withErrors(['captcha' => 'CAPTCHA validation failed. Please try again.']);
        }
        try {
            $this->validate($request, [
                'name' => ['required', 'string', 'min:3'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ], [
                'name.required' => 'Name is required',
                'name.min' => 'Name must be at least 3 characters',
                'email.required' => 'Email is required',
                'email.email' => 'Please enter a valid email address',
                'email.unique' => 'This email is already registered',
                'password.required' => 'Password is required',
                'password.confirmed' => 'Passwords do not match',
                'password.min' => 'Password must be at least 8 characters',
            ]);
        }
        catch(\Exception $e) {
            return redirect()->back()
                ->withInput($request->input())
                ->withErrors($e->getMessage());
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'credit' => 0
            ]);

            // Assign Customer role
            $user->assignRole('Customer');

            // Send verification email using Laravel's built-in verification
            event(new \Illuminate\Auth\Events\Registered($user));

            // Commit transaction
            DB::commit();

            return redirect()->route('login')
                ->with('success', 'Registration successful! Please check your email to verify your account before logging in.');
        }
        catch(\Exception $e) {
            DB::rollBack();
            \Log::error('Registration failed', ['exception' => $e]);
            return redirect()->back()
                ->withInput($request->input())
                ->withErrors('Registration failed. Please try again or contact support.');
        }
    }

    public function login(Request $request) {
        return view('users.login');
    }

    public function doLogin(Request $request) {
        $credentials = $request->only('email', 'password');
        
        if (!Auth::attempt($credentials)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors('Invalid login information.');
        }

        $user = Auth::user();
        
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            
            // Resend verification email
            $user->sendEmailVerificationNotification();
            
            return redirect()->route('login')
                ->withInput($request->only('email'))
                ->with('error', 'Your email is not verified. A new verification link has been sent to your email address.');
        }

        return redirect()->intended('/');
    }

    public function doLogout(Request $request) {

    	Auth::logout();

        return redirect('/');
    }

    public function forgotPassword() {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request) {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ], [
            'email.exists' => 'If your email exists in our system, you will receive a password reset link shortly.'
        ]);

        // Clean up old tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('created_at', '<', now()->subHours(1))
            ->delete();

        $status = PasswordFacade::sendResetLink(
            $request->only('email')
        );

        // Always return the same message to prevent user enumeration
        return back()->with('status', 'If your email exists in our system, you will receive a password reset link shortly.');
    }

    public function showResetForm(Request $request, string $token) {
        // For password reset links, the email is passed as a query parameter
        if (!$request->has('email')) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid password reset link.');
        }

        // Don't hash the token here - Laravel's Password facade already handles this
        // Just check if a record exists with this email
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord || now()->subHours(1)->gt($tokenRecord->created_at)) {
            return redirect()->route('password.request')
                ->with('error', 'This password reset link has expired or is invalid.');
        }
        
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',      // at least one lowercase letter
                'regex:/[A-Z]/',      // at least one uppercase letter
                'regex:/[0-9]/',      // at least one number
                'regex:/[@$!%*#?&]/', // at least one special character
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.'
        ]);

        // Check if the new password is the same as the old one before resetting
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'New password cannot be the same as your old password.'
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // Let Laravel's Password facade handle the token validation
        $status = PasswordFacade::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => \Illuminate\Support\Str::random(60),
                ])->save();

                // Log out all other sessions for this user
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();

                // Clean up used token
                DB::table('password_reset_tokens')
                    ->where('email', $user->email)
                    ->delete();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === PasswordFacade::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully. You can now log in with your new password.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function profile(Request $request, User $user = null) {

        $user = $user??auth()->user();

        if(auth()->id()!=$user->id) {
            if(!auth()->user()->hasPermissionTo('show_users')) abort(401);
        }

        // Prevent employees from viewing admin profiles
        if(auth()->user()->hasRole('Employee') && $user->hasRole('Admin') && auth()->id() != $user->id) {
            return redirect()->route('users')->with('error', 'You are not authorized to view administrator profiles.');
        }

        // Prevent employees from viewing other employee profiles
        if(auth()->user()->hasRole('Employee') && $user->hasRole('Employee') && auth()->id() != $user->id) {
            return redirect()->route('users')->with('error', 'You are not authorized to view other employee profiles.');
        }

        $permissions = [];
        foreach($user->permissions as $permission) {
            $permissions[] = $permission;
        }
        foreach($user->roles as $role) {
            foreach($role->permissions as $permission) {
                $permissions[] = $permission;
            }
        }

        return view('users.profile', compact('user', 'permissions'));
    }

    public function edit(Request $request, User $user = null) {

        $user = $user??auth()->user();

        if(auth()->id()!=$user?->id) {
            if(!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }

        // Prevent employees from editing admins
        if(auth()->user()->hasRole('Employee') && $user->hasRole('Admin')) {
            return redirect()->route('users')->with('error', 'You are not authorized to edit administrator accounts.');
        }

        // Prevent employees from editing other employees
        if(auth()->user()->hasRole('Employee') && $user->hasRole('Employee') && auth()->id() != $user->id) {
            return redirect()->route('users')->with('error', 'You are not authorized to edit other employee accounts.');
        }

        $roles = [];
        foreach(Role::all() as $role) {
            // Prevent employees from assigning the Admin role
            if(auth()->user()->hasRole('Employee') && $role->name == 'Admin') {
                continue;
            }
            $role->taken = ($user->hasRole($role->name));
            $roles[] = $role;
        }

        $permissions = [];
        $directPermissionsIds = $user->permissions()->pluck('id')->toArray();
        foreach(Permission::all() as $permission) {
            $permission->taken = in_array($permission->id, $directPermissionsIds);
            $permissions[] = $permission;
        }

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    public function save(Request $request, User $user) {

        if(auth()->id()!=$user->id) {
            if(!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }

        // Prevent employees from editing admins
        if(auth()->user()->hasRole('Employee') && $user->hasRole('Admin')) {
            return redirect()->route('users')->with('error', 'You are not authorized to edit administrator accounts.');
        }

        // Prevent employees from editing other employees
        if(auth()->user()->hasRole('Employee') && $user->hasRole('Employee') && auth()->id() != $user->id) {
            return redirect()->route('users')->with('error', 'You are not authorized to edit other employee accounts.');
        }

        $user->name = $request->name;
        $user->save();

        if(auth()->user()->hasPermissionTo('admin_users')) {
            $user->syncRoles($request->roles);
            $user->syncPermissions($request->permissions);

            Artisan::call('cache:clear');
        }

        return redirect(route('profile', ['user'=>$user->id]));
    }

    public function delete(Request $request, User $user) {

        if(!auth()->user()->hasPermissionTo('delete_users')) abort(401);

        // Don't allow deleting your own account
        if(auth()->id() == $user->id) {
            return redirect()->route('users')->with('error', 'You cannot delete your own account.');
        }

        // Don't allow admins to delete other admins
        if(auth()->user()->hasRole('Admin') && $user->hasRole('Admin')) {
            return redirect()->route('users')->with('error', 'Administrators cannot delete other administrators.');
        }

        try {
            // Start transaction
            DB::beginTransaction();

            // Delete the user
            $user->delete();

            // Commit transaction
            DB::commit();

            return redirect()->route('users')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            \Log::error('Error deleting user', ['exception' => $e]);
            return redirect()->route('users')->with('error', 'Error deleting user. Please try again or contact support.');
        }
    }

    public function editPassword(Request $request, User $user = null) {

        $user = $user??auth()->user();
        if(auth()->id()!=$user?->id) {
            if(!auth()->user()->hasPermissionTo('edit_users')) abort(401);

            // Prevent employees from changing admin passwords
            if(auth()->user()->hasRole('Employee') && $user->hasRole('Admin')) {
                return redirect()->route('users')->with('error', 'You are not authorized to change administrator passwords.');
            }

            // Prevent employees from changing other employee passwords
            if(auth()->user()->hasRole('Employee') && $user->hasRole('Employee') && auth()->id() != $user->id) {
                return redirect()->route('users')->with('error', 'You are not authorized to change other employee passwords.');
            }
        }

        return view('users.edit_password', compact('user'));
    }

    public function savePassword(Request $request, User $user) {

        if(auth()->id()==$user?->id) {

            $this->validate($request, [
                'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);

            if(!Auth::attempt(['email' => $user->email, 'password' => $request->old_password])) {

                Auth::logout();
                return redirect('/');
            }
        }
        else if(!auth()->user()->hasPermissionTo('edit_users')) {
            abort(401);
        }
        else {
            // Prevent employees from changing admin passwords
            if(auth()->user()->hasRole('Employee') && $user->hasRole('Admin')) {
                return redirect()->route('users')->with('error', 'You are not authorized to change administrator passwords.');
            }

            // Prevent employees from changing other employee passwords
            if(auth()->user()->hasRole('Employee') && $user->hasRole('Employee') && auth()->id() != $user->id) {
                return redirect()->route('users')->with('error', 'You are not authorized to change other employee passwords.');
            }

            $this->validate($request, [
                'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);
        }

        $user->password = bcrypt($request->password); //Secure
        $user->save();

        return redirect(route('profile', ['user'=>$user->id]));
    }

    public function createEmployee(Request $request) {
        // Check if user has admin permission
        if (!auth()->user()->hasRole('Admin')) {
            abort(401);
        }

        return view('users.create_employee');
    }

    public function storeEmployee(Request $request) {
        // Check if user has admin permission
        if (!auth()->user()->hasRole('Admin')) {
            abort(401);
        }

        // Validate input
        try {
            $this->validate($request, [
                'name' => ['required', 'string', 'min:5'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);
        } catch(\Exception $e) {
            return redirect()->back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors('Invalid employee information. Please try again or contact support.');
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Create the employee user
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->credit = 0; // Employees have 0 credit by default
            $user->save();

            // Assign the Employee role
            $user->assignRole('Employee');

            // Commit the transaction
            DB::commit();

            Artisan::call('cache:clear');

            return redirect()->route('users')->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            \Log::error('Error creating employee', ['exception' => $e]);
            return redirect()->back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors('Error creating employee. Please try again or contact support.');
        }
    }

    public function userPurchases(Request $request, User $user) {
        // Check permission
        if(!auth()->user()->hasPermissionTo('show_users')) abort(401);

        // Prevent employees from viewing admin purchases
        if(auth()->user()->hasRole('Employee') && $user->hasRole('Admin')) {
            return redirect()->route('users')->with('error', 'You are not authorized to view administrator purchases.');
        }

        // Get user's purchases with product details
        $purchases = DB::table('purchases')
            ->join('products', 'purchases.product_id', '=', 'products.id')
            ->select(
                'purchases.id',
                'purchases.quantity',
                'purchases.total_price',
                'purchases.status',
                'purchases.created_at',
                'products.name as product_name',
                'products.code as product_code',
                'products.price as product_price'
            )
            ->where('purchases.user_id', $user->id)
            ->orderBy('purchases.created_at', 'desc')
            ->get();

        return view('users.purchases', [
            'purchases' => $purchases,
            'user' => $user
        ]);
    }

    public function chargeCredit(Request $request, User $user) {
        // Only allow charging credit for Customers by Employees or Admins
        if(!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Employee')) {
            abort(401, 'Unauthorized');
        }

        // Only allow charging Customers' credit
        if(!$user->hasRole('Customer')) {
            return redirect()->route('users')->with('error', 'You can only charge credit for customers.');
        }

        // Prevent employees from charging admin accounts
        if(auth()->user()->hasRole('Employee') && $user->hasRole('Admin')) {
            return redirect()->route('users')->with('error', 'You are not authorized to charge administrator accounts.');
        }

        return view('users.charge_credit', compact('user'));
    }

    public function saveCredit(Request $request, User $user) {
        // Only allow charging credit for Customers by Employees or Admins
        if(!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Employee')) {
            abort(401, 'Unauthorized');
        }

        // Only allow charging Customers' credit
        if(!$user->hasRole('Customer')) {
            return redirect()->route('users')->with('error', 'You can only charge credit for customers.');
        }

        // Validate input - ensure amount is positive
        $this->validate($request, [
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        // Start transaction
        DB::beginTransaction();

        try {
            // Add credit to user
            $user->credit += $request->amount;
            $user->save();

            // Commit transaction
            DB::commit();

            return redirect()->route('profile', ['user' => $user->id])
                ->with('success', 'Credit added successfully! New balance: ' . $user->credit);
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();

            \Log::error('Error adding credit', ['exception' => $e]);
            return redirect()->back()->with('error', 'Error adding credit. Please try again or contact support.');
        }
    }
    public function giveGift(Request $request, User $user) {
        // Check if the authenticated user has the manage_sales privilege
        if (!auth()->user()->hasPermissionTo('manage_sales')) {
            return redirect()->route('users')->with('error', 'You do not have permission to give gifts.');
        }

        // Check if a gift has already been given within the last 30 days
        $lastGift = Gift::where('receiver_id', $user->id)
            ->where('gift_given_at', '>', now()->subDays(30))
            ->first();

        if ($lastGift) {
            return redirect()->route('users')->with('error', 'A gift has already been given to this user within the last 30 days.');
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Create gift record
            Gift::create([
                'giver_id' => auth()->id(),
                'receiver_id' => $user->id,
                'amount' => 1000,
                'gift_given_at' => now()
            ]);

            // Add credit to user
            $user->credit += 1000;
            $user->save();

            // Commit transaction
            DB::commit();

            return redirect()->route('users')->with('success', 'Gift of 1,000 coins given to ' . $user->name . '.');
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            \Log::error('Error giving gift', ['exception' => $e]);
            return redirect()->route('users')->with('error', 'Error giving gift. Please try again or contact support.');
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->email)->first();
            
            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'password' => bcrypt(Str::random(16)), // Random password for Google users
                ]);
                
                // Assign Customer role
                $user->assignRole('Customer');
            } else {
                // Update existing user's Google info
                $user->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]);
            }
            
            Auth::login($user);
            
            return redirect('/');
        } catch (\Exception $e) {
            \Log::error('Google authentication failed', ['exception' => $e]);
            return redirect()->route('login')
                ->with('error', 'Google authentication failed. Please try again or contact support.');
        }
    }
}
