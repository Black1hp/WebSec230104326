<?php
namespace App\Http\Controllers\Web;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Termwind\Components\Dd;

class ProductsController extends Controller{

    public function index(Request $request) {
        $products = Product::all();

        return view("products.index", compact('products'));
    }

    public function edit(Request $request, Product $product = null) {
        $product = $product??new Product();
        return view("products.edit", compact('product'));
    }

    public function save(Request $request, Product $product = null) {
        $product = $product??new Product();
        $product->fill($request->all());
        $product->save();
        return redirect()->route('products.index');
    }
    public function delete(Request $request, Product $product) {
        $product->delete();
        return redirect()->route('products.index');
    }

}
