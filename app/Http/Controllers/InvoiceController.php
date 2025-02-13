<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Models\Customer;
use App\Models\Account;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function create(StoreInvoiceRequest $request)
    {
        $accountId = Auth::user()->account_id;

        // Retrieve the account name
        $account = Account::find($accountId);
        $accountName = $account ? $account->name : 'Unknown Account';

        // Find the customer within the same account
        $customer = Customer::where('id', $request->get('customer_id'))
            ->where('account_id', $accountId)
            ->first();

        // If the customer is not found, redirect back with an error message
        if (!$customer) {
            return redirect()->back()->with('error', 'Customer not found or does not belong to your account.');
        }

        // Set the cart instance to be unique for this account
        $cartInstance = 'order';
        $carts =  Cart::instance($cartInstance)->content();

        return view('invoices.index', [
            'customer' => $customer,
            'carts' => $carts,
            'accountName' => $accountName, // Pass the account name to the view
        ]);
    }
}
