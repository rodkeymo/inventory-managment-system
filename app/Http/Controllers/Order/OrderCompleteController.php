<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderCompleteController extends Controller
{
   public function __invoke(Request $request)
   {
       $user = auth()->user();
       
       $orders = Order::where('account_id', $user->account_id)
           ->where('order_status', OrderStatus::COMPLETE)
           ->with('customer')
           ->latest()
           ->get();

       return view('orders.complete-orders', ['orders' => $orders]);
   }
}