<?php
namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use DB;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller {

	use ValidatesRequests;

	public function __construct()
    {
        $this->middleware('auth:web')->except('list');
    }

	public function list(Request $request) {

		$query = Product::select("products.*");

		$query->when($request->keywords,
		fn($q)=> $q->where("name", "like", "%$request->keywords%"));

		$query->when($request->min_price,
		fn($q)=> $q->where("price", ">=", $request->min_price));

		$query->when($request->max_price, fn($q)=>
		$q->where("price", "<=", $request->max_price));

		$query->when($request->order_by,
		fn($q)=> $q->orderBy($request->order_by, $request->order_direction??"ASC"));

		$products = $query->get();

		return view('products.list', compact('products'));
	}

	public function edit(Request $request, Product $product = null) {
		if(!auth()->user()) return redirect('/');

		// Check if user has permission to edit products or is an Employee/Admin
		if(!auth()->user()->hasPermissionTo('edit_products') &&
		   !auth()->user()->hasRole('Employee') &&
		   !auth()->user()->hasRole('Admin')) {
		    abort(401, 'Unauthorized');
		}

		$product = $product??new Product();

		return view('products.edit', compact('product'));
	}

	public function save(Request $request, Product $product = null) {
	    // Check if user has permission to add/edit products or is an Employee/Admin
		if(!auth()->user()->hasPermissionTo('edit_products') &&
		   !auth()->user()->hasRole('Employee') &&
		   !auth()->user()->hasRole('Admin')) {
		    abort(401, 'Unauthorized');
		}

		$this->validate($request, [
	        'code' => ['required', 'string', 'max:32'],
	        'name' => ['required', 'string', 'max:128'],
	        'model' => ['required', 'string', 'max:256'],
	        'description' => ['required', 'string', 'max:1024'],
	        'price' => ['required', 'numeric'],
	        'amount' => ['required', 'integer', 'min:0'],
	    ]);

		$product = $product??new Product();
		$product->fill($request->all());
		$product->save();

		return redirect()->route('products_list')->with('success', 'Product saved successfully!');
	}

	public function delete(Request $request, Product $product) {
		// Check if user has permission to delete products or is an Employee/Admin
		if(!auth()->user()->hasPermissionTo('delete_products') &&
		   !auth()->user()->hasRole('Employee') &&
		   !auth()->user()->hasRole('Admin')) {
		    abort(401, 'Unauthorized');
		}

		try {
		    // Start transaction
		    DB::beginTransaction();

		    // Delete the product
		    $product->delete();

		    // Commit transaction
		    DB::commit();

		    return redirect()->route('products_list')->with('success', 'Product deleted successfully.');
		} catch (\Exception $e) {
		    // Rollback in case of error
		    DB::rollBack();
		    return redirect()->route('products_list')->with('error', 'Error deleting product: ' . $e->getMessage());

		}
	}

	public function purchase(Request $request, Product $product) {
        // Validate input
        $this->validate($request, [
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = Auth::user();

        // Check if user has Customer role
        if (!$user->hasRole('Customer')) {
            return redirect()->back()->with('error', 'Only customers can purchase products.');
        }

        // Calculate total price
        $quantity = $request->input('quantity', 1);

        // Check if there's enough product amount available
        if ($product->amount < $quantity) {
            return redirect()->back()->with('error', 'Not enough product in stock. Available: ' . $product->amount);
        }

        $totalPrice = $product->price * $quantity;

        // Check if user has enough credit
        if (!$user->hasEnoughCredit($totalPrice)) {
            return redirect()->back()->with('error', 'You do not have enough credit to make this purchase.');
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Deduct credit from user
            if (!$user->deductCredit($totalPrice)) {
                throw new \Exception('Failed to deduct credit from your account.');
            }

            // Reduce product amount
            $product->amount -= $quantity;
            $product->save();

            // Create purchase record
            Purchase::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'status' => 'completed'
            ]);

            // Commit transaction
            DB::commit();

            return redirect()->back()->with('success', 'Purchase completed successfully! Your remaining credit is ' . $user->credit);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            return redirect()->back()->with('error', 'Purchase failed: ' . $e->getMessage());
        }
    }

    public function myPurchases() {
        // Ensure user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has Customer role
        $user = Auth::user();
        if (!$user->hasRole('Customer')) {
            return redirect()->route('products_list')->with('error', 'Only customers can view purchase history.');
        }

        // Get user's purchases with product details
        $purchases = Purchase::with('product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('products.my-purchases', [
            'purchases' => $purchases,
            'user' => $user
        ]);
    }
}
