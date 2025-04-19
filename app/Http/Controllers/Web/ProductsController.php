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
    
    /**
     * Return a purchased product - increase stock and refund credit
     */

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
    
    /**
     * Toggle the liked status for a purchase
     * Ensures user can only like a product once, regardless of purchase count
     */
    public function toggleLike(Request $request, Purchase $purchase) {
        // Ensure that the purchase belongs to the current user
        if (Auth::id() !== $purchase->user_id) {
            return redirect()->back()->with('error', 'You can only like products you have purchased.');
        }
        
        // Cannot like a returned product if it wasn't already liked
        if ($purchase->status === 'returned' && !$purchase->liked) {
            return redirect()->back()->with('error', 'You cannot like a product after returning it.');
        }
        
        if ($purchase->liked) {
            // Unliking: Set this purchase to unliked
            $purchase->liked = false;
            $purchase->save();
            
            // Also update any other purchases of this product by this user for consistency
            Purchase::where('user_id', Auth::id())
                ->where('product_id', $purchase->product_id)
                ->where('id', '!=', $purchase->id)
                ->where('liked', true)
                ->update(['liked' => false]);
                
            return redirect()->back()->with('success', 'You have unliked the product.');
        } else {
            // Liking: First, unlike any other purchases of this product
            Purchase::where('user_id', Auth::id())
                ->where('product_id', $purchase->product_id)
                ->where('liked', true)
                ->update(['liked' => false]);
            
            // Then set this purchase to liked
            $purchase->liked = true;
            $purchase->save();
            
            return redirect()->back()->with('success', 'You have liked the product.');
        }
    }
    
    /**
     * Like or unlike a product directly from the product list
     * Prevents multiple likes of the same product by a single user
     */
    public function toggleProductLike(Request $request, Product $product) {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to like products.');
        }
        
        // Check if user has Customer role
        $user = Auth::user();
        if (!$user->hasRole('Customer')) {
            return redirect()->back()->with('error', 'Only customers can like products.');
        }
        
        // First, check if the user has already liked this product
        $existingLike = Purchase::where('user_id', Auth::id())
                      ->where('product_id', $product->id)
                      ->where('liked', true)
                      ->first();
        
        if ($existingLike) {
            // User has already liked the product, so unlike it
            $existingLike->liked = false;
            $existingLike->save();
            
            // Also unlike any other purchases of this product by this user (for consistency)
            Purchase::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->where('liked', true)
                ->where('id', '!=', $existingLike->id)
                ->update(['liked' => false]);
            
            return redirect()->back()->with('success', 'You have unliked this product.');
        }
        
        // Find the user's most recent non-returned purchase or most recent purchase if all are returned
        $purchase = Purchase::where('user_id', Auth::id())
                  ->where('product_id', $product->id)
                  ->where('status', '!=', 'returned')
                  ->orderBy('created_at', 'desc')
                  ->first();
        
        // If no active purchase found, get any purchase including returned ones
        if (!$purchase) {
            $purchase = Purchase::where('user_id', Auth::id())
                      ->where('product_id', $product->id)
                      ->orderBy('created_at', 'desc')
                      ->first();
        }
        
        // Check if user has purchased this product
        if (!$purchase) {
            return redirect()->back()->with('error', 'You can only like products you have purchased.');
        }
        
        // Cannot like a returned product if it wasn't already liked
        if ($purchase->status === 'returned') {
            return redirect()->back()->with('error', 'You cannot like a product after returning it.');
        }
        
        // Set liked to true
        $purchase->liked = true;
        $purchase->save();
        
        return redirect()->back()->with('success', 'You have liked this product.');
    }
    
    /**
     * Return a purchased product
     * Adds the product back to stock and refunds the user's credit
     */
    public function returnProduct(Request $request, Purchase $purchase) {
        // Verify the CSRF token is valid
        if (!$request->hasValidSignature() && env('APP_ENV') === 'production') {
            // Log potential CSRF attempt
            \Log::warning('Potential CSRF attempt detected in product return', [
                'ip' => $request->ip(),
                'user_id' => Auth::id(),
                'purchase_id' => $purchase->id
            ]);
            return redirect()->back()->with('error', 'Security validation failed. Please try again.');
        }
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Verify if user has permission to return the product
            // Either the purchase belongs to the user or the user is an employee/admin
            $isOwnPurchase = Auth::id() === $purchase->user_id;
            $canReturnForOthers = Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Admin');
            
            if (!$isOwnPurchase && !$canReturnForOthers) {
                // Log unauthorized access attempt
                \Log::warning('Unauthorized return attempt', [
                    'user_id' => Auth::id(),
                    'attempted_purchase_id' => $purchase->id,
                    'purchase_owner_id' => $purchase->user_id
                ]);
                return redirect()->back()->with('error', 'You do not have permission to return this product.');
            }
            
            // Check if the purchase is already returned or older than 30 days
            if ($purchase->status === 'returned') {
                return redirect()->back()->with('error', 'This product has already been returned.');
            }
            
            // Validate return time window (30 days)
            $purchaseDate = new \DateTime($purchase->created_at);
            $now = new \DateTime();
            $daysSincePurchase = $purchaseDate->diff($now)->days;
            
            if ($daysSincePurchase > 30 && !$canReturnForOthers) {
                return redirect()->back()->with('error', 'Returns are only allowed within 30 days of purchase. Please contact an employee for assistance.');
            }
            
            // Check if the product still exists in the database
            $product = $purchase->product;
            if (!$product) {
                return redirect()->back()->with('error', 'The purchased product no longer exists in the system.');
            }
            
            // Validate quantity is positive and not manipulated
            if ($purchase->quantity <= 0 || $purchase->quantity > 1000) {
                // Log potential data tampering
                \Log::warning('Invalid quantity in return request', [
                    'user_id' => Auth::id(),
                    'purchase_id' => $purchase->id,
                    'quantity' => $purchase->quantity
                ]);
                return redirect()->back()->with('error', 'Invalid quantity detected.');
            }
            
            // Validate total_price makes sense for the quantity
            $expectedPrice = $product->price * $purchase->quantity;
            $priceDifference = abs($expectedPrice - $purchase->total_price);
            $allowedVariance = $expectedPrice * 0.01; // 1% variance allowed for historical price changes
            
            if ($priceDifference > $allowedVariance) {
                // Log potential price manipulation
                \Log::warning('Price manipulation detected in return', [
                    'user_id' => Auth::id(),
                    'purchase_id' => $purchase->id,
                    'expected_price' => $expectedPrice,
                    'actual_price' => $purchase->total_price
                ]);
                return redirect()->back()->with('error', 'Invalid price data detected.');
            }
            
            // Get the user who made the purchase
            $user = $purchase->user;
            if (!$user) {
                return redirect()->back()->with('error', 'The user account associated with this purchase no longer exists.');
            }
            
            // Add the product back to stock
            $product->amount += $purchase->quantity;
            $product->save();
            
            // Refund the user
            $user->credit += $purchase->total_price;
            $user->save();
            
            // Update purchase status to 'returned' and remove any like
            $purchase->status = 'returned';
            
            // If the purchase was liked, unlike it
            if ($purchase->liked) {
                $purchase->liked = false;
                \Log::info('Like removed due to product return', [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'purchase_id' => $purchase->id
                ]);
            }
            
            $purchase->save();
            
            // Log successful return for audit
            \Log::info('Product returned successfully', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'purchase_id' => $purchase->id,
                'refund_amount' => $purchase->total_price,
                'processed_by' => Auth::id()
            ]);
            
            // Commit transaction
            DB::commit();
            
            return redirect()->back()->with('success', 'Product returned successfully. Credit refunded: ' . $purchase->total_price);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            // Log the exception
            \Log::error('Return failed with exception', [
                'user_id' => Auth::id(),
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Return failed: ' . $e->getMessage());
        }
    }
}
