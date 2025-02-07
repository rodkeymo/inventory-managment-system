<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;

class UnitController extends Controller
{
    public function index()
    {
        $user = auth()->user(); // Get the logged-in user
        $units = Unit::where('account_id', $user->account_id)
            ->select(['id', 'name', 'slug', 'short_code'])
            ->get();
    
        return view('units.index', [
            'units' => $units,
        ]);
    }
    
    public function create()
    {
        return view('units.create');
    }

    public function show(Unit $unit)
    {
        $user = auth()->user();

        // Ensure the unit belongs to the authenticated user's account
        if ($unit->account_id !== $user->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $unit->loadMissing('products');

        return view('units.show', [
            'unit' => $unit
        ]);
    }

    public function store(StoreUnitRequest $request)
    {
        Unit::create(array_merge($request->validated(), ['account_id' => auth()->user()->account_id]));

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit has been created!');
    }


    public function edit(Unit $unit)
    {
        return view('units.edit', [
            'unit' => $unit
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $user = auth()->user();

        if ($unit->account_id !== $user->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $unit->update($request->all());

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit has been updated!');
    }


    public function destroy(Unit $unit)
    {
        $user = auth()->user();
    
        if ($unit->account_id !== $user->account_id) {
            abort(403, 'Unauthorized action.');
        }
    
        $unit->delete();
    
        return redirect()
            ->route('units.index')
            ->with('success', 'Unit has been deleted!');
    }
    
}
