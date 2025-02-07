<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends Controller
{
    public function index()
    {
        // Fetch users only within the same account as the logged-in user
        $users = User::where('account_id', auth()->user()->account_id)
            ->with('role')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $accountId = auth()->user()->account_id; // Ensure new user belongs to the same account

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'account_id' => $accountId, // Assign the same account ID
        ]);

        // Handle image upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile/', $filename, 'public');

            $user->update(['photo' => $filename]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'New User has been created!');
    }

    public function show(User $user)
    {
        // Restrict access to users within the same account
        if ($user->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // Restrict access to users within the same account
        if ($user->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        // Restrict access to users within the same account
        if ($user->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        $user->update($request->except('photo'));

        // Handle image upload
        if ($request->hasFile('photo')) {
            // Delete Old Photo if it exists
            if ($user->photo) {
                Storage::disk('public')->delete('profile/' . $user->photo);
            }

            // Store New Photo
            $file = $request->file('photo');
            $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile/', $fileName, 'public');

            // Update user record
            $user->update(['photo' => $fileName]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User has been updated!');
    }

    public function updatePassword(Request $request, String $username)
    {
        $validated = $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        // Find the user and ensure they belong to the same account
        $user = User::where('username', $username)
            ->where('account_id', auth()->user()->account_id)
            ->firstOrFail();

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User password has been updated!');
    }

    public function destroy(User $user)
    {
        // Restrict access to users within the same account
        if ($user->account_id !== auth()->user()->account_id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete User Photo
        if ($user->photo) {
            Storage::disk('public')->delete('profile/' . $user->photo);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User has been deleted!');
    }
}
