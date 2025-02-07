<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Restrict the products based on the authenticated user's account_id
        $products = Product::where('account_id', $user->account_id);

        // Apply the category filter if provided in the request
        if ($request->has('category_id')) {
            $products = $products->where('category_id', $request->get('category_id'));
        }

        // Get the filtered products
        $products = $products->get();

        return response()->json($products);
    }
}
