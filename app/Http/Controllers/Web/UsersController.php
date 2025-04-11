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

use App\Http\Controllers\Controller;
use App\Models\User;

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

    	try {
    		$this->validate($request, [
	        'name' => ['required', 'string', 'min:5'],
	        'email' => ['required', 'email', 'unique:users'],
	        'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
	    	]);
    	}
    	catch(\Exception $e) {

    		return redirect()->back()->withInput($request->input())->withErrors('Invalid registration information.');
    	}

        // Begin transaction
        DB::beginTransaction();

        try {
        	// Create the user
        	$user = new User();
    	    $user->name = $request->name;
    	    $user->email = $request->email;
    	    $user->password = bcrypt($request->password); //Secure
    	    $user->credit = 0; // Default credit for new customers
    	    $user->save();

    	    // Assign Customer role (ID: 3)
    	    $user->assignRole('Customer');

    	    // Commit the transaction
    	    DB::commit();

    	    // Clear permission cache
    	    Artisan::call('cache:clear');

    	    return redirect('/')->with('success', 'Registration successful! You can now log in.');
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            return redirect()->back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors('Registration failed: ' . $e->getMessage());
        }
    }

    public function login(Request $request) {
        return view('users.login');
    }

    public function doLogin(Request $request) {

    	if(!Auth::attempt(['email' => $request->email, 'password' => $request->password]))
            return redirect()->back()->withInput($request->input())->withErrors('Invalid login information.');

        $user = User::where('email', $request->email)->first();
        Auth::setUser($user);

        return redirect('/');
    }

    public function doLogout(Request $request) {

    	Auth::logout();

        return redirect('/');
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
            return redirect()->route('users')->with('error', 'Error deleting user: ' . $e->getMessage());
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
                ->withErrors('Invalid employee information: ' . $e->getMessage());
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
            return redirect()->back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors('Error creating employee: ' . $e->getMessage());
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

            return redirect()->back()->with('error', 'Error adding credit: ' . $e->getMessage());
        }
    }
    public function giveGift(Request $request, User $user) {
        // Check if the authenticated user has the manage_sales privilege
        if (!auth()->user()->hasPermissionTo('manage_sales')) {
            return redirect()->route('users')->with('error', 'You do not have permission to give gifts.');
        }

        // Check if a gift has already been given within the last 30 days
        $lastGiftGivenAt = $user->last_gift_given_at;
        if ($lastGiftGivenAt && $lastGiftGivenAt->diffInDays(now()) < 30) {
            return redirect()->route('users')->with('error', 'A gift has already been given to this user within the last 30 days.');
        }

        // Add 1,000 coins to the user's account
        $user->credit += 1000;
        $user->last_gift_given_at = now();
        $user->save();

        return redirect()->route('users')->with('success', 'Gift of 1,000 coins given to ' . $user->name . '.');
    }
}
