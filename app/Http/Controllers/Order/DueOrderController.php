<?php

namespace App\Http\Controllers\Order;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DueOrderController extends Controller
{
   public function index()
   {
       $user = auth()->user();
       
       $orders = Order::where('account_id', $user->account_id)
           ->where('due', '>', '0')
           ->latest()
           ->with('customer')
           ->paginate();

       return view('due.index', ['orders' => $orders]);
   }

   public function show(Order $order)
   {
       $order->loadMissing(['customer', 'details'])->get();
       return view('due.show', ['order' => $order]);
   }

   public function edit(Order $order) 
   {
       $user = auth()->user();
       
       $order->loadMissing(['customer', 'details'])->get();
       $customers = Customer::where('account_id', $user->account_id)
           ->select(['id', 'name'])
           ->get();

       return view('due.edit', [
           'order' => $order,
           'customers' => $customers
       ]);
   }

   public function update(Order $order, Request $request)
   {
       $request->validate(['pay' => 'required|numeric']);

       $paidDue = $order->due - $request->pay;
       $paidPay = $order->pay + $request->pay;

       $order->update([
           'due' => $paidDue,
           'pay' => $paidPay
       ]);

       return redirect()
           ->route('due.index')
           ->with('success', 'Due amount has been updated!');
   }
}