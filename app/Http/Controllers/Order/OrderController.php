<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Customer;
use App\Models\Account;
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
       $user = auth()->user();
       $orders = Order::where('account_id', $user->account_id)
           ->latest()
           ->get();

       return view('orders.index', ['orders' => $orders]);
   }

   public function create()
   {
       $user = auth()->user();
       Cart::instance('order')->destroy();

       return view('orders.create', [
           'carts' => Cart::content(),
           'customers' => Customer::where('account_id', $user->account_id)
               ->get(['id', 'name']),
           'products' => Product::where('account_id', $user->account_id)
               ->with(['category', 'unit'])
               ->get(),
       ]);
   }

   public function store(OrderStoreRequest $request)
   {
       try {
           DB::beginTransaction();
           
           $user = auth()->user();
           $orderData = array_merge($request->all(), ['account_id' => $user->account_id]);
           
           $order = Order::create($orderData);

           if (in_array($request->payment_type, ['Mpesa', 'Bank', 'HandCash'])) {
               $order->order_status = 1;
               $order->invoice_no = 'RCPT-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
           } elseif ($request->payment_type === 'Credit') {
               $order->invoice_no = 'INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
           }

           if ($order->due > 0) {
               $order->order_status = 0;
           }

           $order->save();

           $contents = Cart::instance('order')->content();
           $oDetails = [];

           foreach ($contents as $content) {
               $soldAt = $request->input("sold_at_{$content->id}");
               $discount = (($content->price - $soldAt) / $content->price) * 100;

               $oDetails[] = [
                   'order_id' => $order->id,
                   'product_id' => $content->id,
                   'quantity' => $content->qty,
                   'unitcost' => $content->price,
                   'sold_at' => $soldAt,
                   'discount_percent' => $discount,
                   'total' => $soldAt * $content->qty,
                   'created_at' => Carbon::now(),
                   'account_id' => $user->account_id
               ];

               $product = Product::where('account_id', $user->account_id)
                   ->find($content->id);
                   
               if ($product) {
                   $product->quantity -= $content->qty;
                   $product->save();
               }
           }

           OrderDetails::insert($oDetails);
           DB::commit();
           Cart::destroy();

           $order = $order->fresh(['customer', 'details']);
           return response()->view('orders.print-invoice', compact('order'))
               ->header('Content-Type', 'text/html');

       } catch (\Exception $e) {
           DB::rollBack();
           \Log::error('Order creation failed: ' . $e->getMessage());
           return redirect()
               ->route('orders.create')
               ->with('error', 'Failed to create order: ' . $e->getMessage());
       }
   }

   public function show(Order $order)
   {
       try {
           $order->loadMissing(['customer', 'details']);
           return view('orders.show', [
               'order' => $order,
               'error' => null
           ]);
       } catch (\Exception $e) {
           \Log::error('Error retrieving order: ' . $e->getMessage());
           return view('orders.show', [
               'order' => null,
               'error' => $e->getMessage()
           ]);
       }
   }

   public function update(Order $order, Request $request)
   {
       $user = auth()->user();
       $products = OrderDetails::where('order_id', $order->id)
           ->where('account_id', $user->account_id)
           ->get();

       foreach ($products as $product) {
           Product::where('id', $product->product_id)
               ->where('account_id', $user->account_id)
               ->update(['quantity' => DB::raw('quantity-' . $product->quantity)]);
       }

       $order->update(['order_status' => OrderStatus::COMPLETE]);

       return redirect()
           ->route('orders.complete')
           ->with('success', 'Order completed successfully');
   }

   public function destroy(Order $order)
   {
       try {
           DB::beginTransaction();
           $user = auth()->user();

           foreach ($order->details as $detail) {
               Product::where('id', $detail->product_id)
                   ->where('account_id', $user->account_id)
                   ->increment('quantity', $detail->quantity);
                   
               $detail->delete();
           }

           $order->delete();
           DB::commit();

           return redirect()
               ->route('orders.index')
               ->with('success', 'Order deleted successfully');

       } catch (\Exception $e) {
           DB::rollBack();
           return redirect()
               ->route('orders.index')
               ->with('error', 'Failed to delete order: ' . $e->getMessage());
       }
   }

   public function downloadInvoice($order)
    {
        $accountId = Auth::user()->account_id;
        $user = auth()->user();

        // Retrieve the account name
        $account = Account::find($accountId);
        $accountName = $account ? $account->name : 'Unknown Account';

        // Fetch the order with related customer and details
        $order = Order::with(['customer', 'details'])
            ->where('id', $order)
            ->where('account_id', $user->account_id)
            ->firstOrFail();

        // Pass the account name to the view
        return view('orders.print-invoice', [
            'order' => $order,
            'accountName' => $accountName
        ]);
    }
}