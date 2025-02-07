<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorHTML;

class ProductController extends Controller
{
   public function index()
   {
       $user = auth()->user();
       $products = Product::where('account_id', $user->account_id)
           ->select('id', 'name', 'unit_id')
           ->with('unit')
           ->limit(1)
           ->get();

       return view('products.index', compact('products'));
   }

   public function create(Request $request)
   {
       $user = auth()->user();
       $categories = Category::where('account_id', $user->account_id)->get(['id', 'name']);
       $units = Unit::where('account_id', $user->account_id)->get(['id', 'name']);

       if ($request->has('category')) {
           $categories = Category::where('account_id', $user->account_id)
               ->whereSlug($request->get('category'))
               ->get();
       }

       if ($request->has('unit')) {
           $units = Unit::where('account_id', $user->account_id)
               ->whereSlug($request->get('unit'))
               ->get();
       }

       return view('products.create', [
           'categories' => $categories,
           'units' => $units,
       ]);
   }

   public function store(StoreProductRequest $request)
   {
       try {
           $user = auth()->user();
           $code = $request->input('code') ?? 'PC' . hexdec(uniqid());
           
           $productData = array_merge($request->all(), ['account_id' => $user->account_id]);
           $productData['code'] = $code;

           $product = Product::create($productData);

           if ($request->hasFile('product_image')) {
               $file = $request->file('product_image');
               $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
               $file->storeAs('products/', $filename, 'public');
               $product->update(['product_image' => $filename]);
           }

           return redirect()->back()->with('success', 'Product created successfully');
       } catch (\Exception $e) {
           \Log::error('Error creating product: ' . $e->getMessage());
           return redirect()->back()->with('error', 'Failed to create product: ' . $e->getMessage());
       }
   }

   public function show(Product $product)
   {
       $generator = new BarcodeGeneratorHTML();
       $barcode = $generator->getBarcode($product->code, $generator::TYPE_CODE_128);

       return view('products.show', [
           'product' => $product,
           'barcode' => $barcode,
       ]);
   }

   public function edit(Product $product)
   {
       $user = auth()->user();
       
       return view('products.edit', [
           'categories' => Category::where('account_id', $user->account_id)->get(),
           'units' => Unit::where('account_id', $user->account_id)->get(),
           'product' => $product
       ]);
   }

   public function update(UpdateProductRequest $request, Product $product)
   {
       $product->update($request->except('product_image'));

       if ($request->hasFile('product_image')) {
           if ($product->product_image) {
               unlink(public_path('storage/products/') . $product->product_image);
           }

           $file = $request->file('product_image');
           $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
           $file->storeAs('products/', $fileName, 'public');
           $product->update(['product_image' => $fileName]);
       }

       return redirect()
           ->route('products.index')
           ->with('success', 'Product updated successfully');
   }

   public function destroy(Product $product)
   {
       if ($product->product_image) {
           unlink(public_path('storage/products/') . $product->product_image);
       }

       $product->delete();

       return redirect()
           ->route('products.index')
           ->with('success', 'Product deleted successfully');
   }
}