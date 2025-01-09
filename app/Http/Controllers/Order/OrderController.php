<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->get();

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function create()
    {
        Cart::instance('order')
            ->destroy();

        return view('orders.create', [
            'carts' => Cart::content(),
            'customers' => Customer::all(['id', 'name']),
            'products' => Product::with(['category', 'unit'])->get(),
        ]);
    }

public function store(OrderStoreRequest $request)
{
    try {
        // Start a transaction for safety
        DB::beginTransaction();

        // Create the order
        $order = Order::create($request->all());

        // Set the invoice number prefix based on payment type
        if (in_array($request->payment_type, ['Mpesa', 'Bank', 'HandCash'])) {
            $order->order_status = 1; // Default status for these payment types
            $order->invoice_no = 'RCPT-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
        } elseif ($request->payment_type === 'Credit') {
            $order->invoice_no = 'INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
        }

        // Check if due is not zero and update the order status
        if ($order->due > 0) {
            $order->order_status = 0; // Status 0 for orders with outstanding balances
        }

        // Save any additional changes to the order
        $order->save();

        // Create Order Details
        $contents = Cart::instance('order')->content();
        $oDetails = [];

        foreach ($contents as $content) {
            $soldAt = $request->input("sold_at_{$content->id}");
            $discount = (($content->price - $soldAt) / $content->price) * 100; // Discount in percentage

            $oDetails[] = [
                'order_id' => $order->id,
                'product_id' => $content->id,
                'quantity' => $content->qty,
                'unitcost' => $content->price,
                'sold_at' => $soldAt,
                'discount_percent' => $discount,
                'total' => $soldAt * $content->qty,
                'created_at' => Carbon::now(),
            ];

            // Update the product quantity after the order has been placed
            $product = Product::find($content->id);
            if ($product) {
                $product->quantity -= $content->qty;
                $product->save();
            }
        }

        // Insert all order details at once for better performance
        OrderDetails::insert($oDetails);

        // Commit the transaction
        DB::commit();

        // Reload the order with all required data (fresh ensures related data is included)
        $order = $order->fresh(['customer', 'details']);

        // Clear the cart after everything has been successfully processed
        Cart::destroy();

        // Redirect to the invoice view for printing
        return response()->view('orders.print-invoice', compact('order'))
            ->header('Content-Type', 'text/html');

    } catch (\Exception $e) {
        // Rollback the transaction if an error occurs
        DB::rollBack();

        // Log the error for debugging
        \Log::error('Order creation failed: ' . $e->getMessage());

        // Redirect back to the order creation page with the error
        return redirect()
            ->route('orders.create')
            ->with('error', 'Failed to create the order. Error: ' . $e->getMessage());
    }
}



    


   public function show(Order $order)
    {
        try {
            // Attempt to load the necessary relationships
            $order->loadMissing(['customer', 'details']);
    
            // Return the view with order details
            return view('orders.show', [
                'order' => $order,
                'error' => null, // No errors occurred
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Error retrieving order details: ' . $e->getMessage());
    
            // Return the view with the error message
            return view('orders.show', [
                'order' => null, // No valid order data
                'error' => $e->getMessage(), // Pass the error message to the view
            ]);
        }
    }


    public function update(Order $order, Request $request)
    {
        // TODO refactoring

        // Reduce the stock
        $products = OrderDetails::where('order_id', $order)->get();

        foreach ($products as $product) {
            Product::where('id', $product->product_id)
                ->update(['quantity' => DB::raw('quantity-' . $product->quantity)]);
        }

        $order->update([
            'order_status' => OrderStatus::COMPLETE,
        ]);

        return redirect()
            ->route('orders.complete')
            ->with('success', 'Order has been completed!');
    }

    public function destroy(Order $order)
    {
        $order->delete();
    }

    public function downloadInvoice($order)
    {
        $order = Order::with(['customer', 'details'])
            ->where('id', $order)
            ->first();

        return view('orders.print-invoice', [
            'order' => $order,
        ]);
    }
}
