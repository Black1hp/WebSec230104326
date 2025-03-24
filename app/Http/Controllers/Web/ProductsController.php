<?php

namespace App\Http\Controllers\Web;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::all();

        return view('products.index', [
            'products' => $products
        ]);
    }

    public function edit(Product $product = null)
    {
        $product ? $this->authorize('update', $product) : $this->authorize('create', Product::class);

        return view('products.edit', [
            'product' => $product ?? new Product()
        ]);
        return redirect()->route('products.index')->with('success', 'Product Updated successfully.');
    }

    public function save(Request $request, Product $product = null)
    {
        $this->validate($request, [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'model' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        $product ? $this->authorize('update', $product) : $this->authorize('create', Product::class);

        $product = $product ?? new Product();
        $product->fill($request->except('photo'));

        if ($request->hasFile('photo')) {
            $product->photo = $request->file('photo')->store('products');
        }

        $product->save();

        return redirect()->route('products.index')->with('success', 'Product saved successfully.');
    }

    public function delete(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
