<?php

namespace App\Http\Controllers\Web;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $purchases = $user->purchases()->with('product')->get();
        return view('purchases.index', compact('purchases'));
    }

    public function store(Request $request, Product $product)
    {
        $user = Auth::user();
        $quantity = $request->input('quantity', 1);
        $totalCost = $product->price * $quantity;

        // Validate purchase conditions
        if (!$product->isInStock()) {
            return back()->with('error', 'Product is out of stock.');
        }

        if (!$user->hasSufficientCredit($totalCost)) {
            return back()->with('error', 'Insufficient credit. Please add more credit to your account.');
        }

        // Create purchase record
        $purchase = Purchase::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_paid' => $product->price,
        ]);

        // Update product stock
        $product->stock -= $quantity;
        $product->save();

        // Deduct credit from user
        $user->deductCredit($totalCost);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase completed successfully!');
    }
} 