<?php
namespace App\Http\Controllers\Web;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Termwind\Components\Dd;

class ProductsController extends Controller{

    public function index(Request $request) {
        $this->authorize('viewAny', Product::class); // Changed from 'view' to 'viewAny'
        $products = Product::all();
        return view("products.index", compact('products'));
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);
        return view('products.show', compact('product'));
    }

    public function edit(Request $request, Product $product = null) {
        if ($product) {
            $this->authorize('update', $product);
        } else {
            $this->authorize('create', Product::class);
        }
        $product = $product ?? new Product();
        return view("products.edit", compact('product'));
    }

    public function save(Request $request, Product $product = null) {
        if ($product) {
            $this->authorize('update', $product);
        } else {
            $this->authorize('create', Product::class);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64', 'unique:products,code,' . ($product->id ?? 'null')],
            'name' => ['required', 'string', 'max:256'],
            'model' => ['required', 'string', 'max:128'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string'],
            'photo' => ['required', 'string', 'max:128'],
        ]);

        $product = $product ?? new Product();
        $product->fill($validated);
        $product->save();

        return redirect()->route('products.index')
            ->with('success', $product->wasRecentlyCreated ? 'Product created successfully.' : 'Product updated successfully.');
    }

    public function delete(Request $request, Product $product) {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
