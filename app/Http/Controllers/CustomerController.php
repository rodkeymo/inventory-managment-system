<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        // Fetch customers only within the same account as the logged-in user
        $customers = Customer::where('account_id', auth()->user()->account_id)
            ->get();

        return view('customers.index', [
            'customers' => $customers
        ]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        // Retrieve the account_id of the authenticated user
        $accountId = auth()->user()->account_id;

        // Create the customer with the validated data and the account_id
        $customer = Customer::create([
            ...$request->validated(), // Spread the validated request data
            'account_id' => $accountId, // Add the account_id
        ]);

        // Handle upload of an image
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

            $file->storeAs('customers/', $filename, 'public');
            $customer->update([
                'photo' => $filename
            ]);
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'New customer has been created!');
    }

    public function show(Customer $customer)
    {
        // Restrict access to customers within the same account
        if ($customer->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $customer->loadMissing(['quotations', 'orders'])->get();

        return view('customers.show', [
            'customer' => $customer
        ]);
    }

    public function edit(Customer $customer)
    {
        // Restrict access to customers within the same account
        if ($customer->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('customers.edit', [
            'customer' => $customer
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        // Restrict access to customers within the same account
        if ($customer->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $customer->update($request->except('photo'));

        // Handle image upload
        if ($request->hasFile('photo')) {
            // Delete Old Photo
            if ($customer->photo) {
                Storage::disk('public')->delete('customers/' . $customer->photo);
            }

            // Prepare New Photo
            $file = $request->file('photo');
            $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

            // Store the new image in Storage
            $file->storeAs('customers/', $fileName, 'public');

            // Save the new photo to the database
            $customer->update([
                'photo' => $fileName
            ]);
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer has been updated!');
    }

    public function destroy(Customer $customer)
    {
        // Restrict access to customers within the same account
        if ($customer->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the customer's photo if it exists
        if ($customer->photo) {
            Storage::disk('public')->delete('customers/' . $customer->photo);
        }

        $customer->delete();

        return redirect()
            ->back()
            ->with('success', 'Customer has been deleted!');
    }
}