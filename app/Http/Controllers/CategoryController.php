<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index()
    {
        // Fetch categories only within the same account as the logged-in user
        $categories = Category::where('account_id', auth()->user()->account_id)
            ->get();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        // Retrieve the account_id of the authenticated user
        $accountId = auth()->user()->account_id;

        // Create the category with the validated data and the account_id
        Category::create([
            ...$request->validated(), // Spread the validated request data
            'account_id' => $accountId, // Add the account_id
        ]);

        // Redirect with a success message
        return redirect()
            ->route('categories.index')
            ->with('success', 'Category has been created!');
    }

    public function show(Category $category)
    {
        // Restrict access to categories within the same account
        if ($category->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('categories.show', [
            'category' => $category
        ]);
    }

    public function edit(Category $category)
    {
        // Restrict access to categories within the same account
        if ($category->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('categories.edit', [
            'category' => $category
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // Restrict access to categories within the same account
        if ($category->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category has been updated!');
    }

    public function destroy(Category $category)
    {
        // Restrict access to categories within the same account
        if ($category->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category has been deleted!');
    }
}