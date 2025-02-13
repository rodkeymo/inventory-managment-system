<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('account_id', auth()->user()->account_id)
            ->get();

        return view('suppliers.index', [
            'suppliers' => $suppliers
        ]);
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        // Retrieve the account_id of the authenticated user
        $accountId = auth()->user()->account_id;

        // Create the supplier with the validated data and the account_id
        $supplier = Supplier::create([
            ...$request->validated(), // Spread the validated request data
            'account_id' => $accountId, // Add the account_id
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

            $file->storeAs('suppliers/', $filename, 'public');
            $supplier->update([
                'photo' => $filename
            ]);
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'New supplier has been created!');
    }

    public function show(Supplier $supplier)
    {
        // Restrict access to suppliers within the same account
        if ($supplier->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $supplier->loadMissing('purchases')->get();

        return view('suppliers.show', [
            'supplier' => $supplier
        ]);
    }

    public function edit(Supplier $supplier)
    {
        // Restrict access to suppliers within the same account
        if ($supplier->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('suppliers.edit', [
            'supplier' => $supplier
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        // Restrict access to suppliers within the same account
        if ($supplier->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $supplier->update($request->except('photo'));

        if ($request->hasFile('photo')) {
            // Delete Old Photo
            if ($supplier->photo) {
                Storage::disk('public')->delete('suppliers/' . $supplier->photo);
            }

            // Prepare New Photo
            $file = $request->file('photo');
            $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

            // Store the new image in Storage
            $file->storeAs('suppliers/', $fileName, 'public');

            // Save the new photo to the database
            $supplier->update([
                'photo' => $fileName
            ]);
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier has been updated!');
    }

    public function destroy(Supplier $supplier)
    {
        // Restrict access to suppliers within the same account
        if ($supplier->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the supplier's photo if it exists
        if ($supplier->photo) {
            Storage::disk('public')->delete('suppliers/' . $supplier->photo);
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier has been deleted!');
    }
}