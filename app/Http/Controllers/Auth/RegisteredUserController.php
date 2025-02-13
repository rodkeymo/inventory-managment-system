<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Account;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'alpha_dash:ascii'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'accountName' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'string', 'exists:roles,name'],
            'terms-of-service' => ['required']
        ]);

        // Ensure only admins can register here
        $role = Role::where('name', $request->role_id)->first();
        if (!$role || strtolower($role->name) !== 'admin') {
            return back()->withErrors(['role_id' => 'Only Admins can register from this form.']);
        }

        // Create a new Account for the Admin
        $account = Account::create(['name' => $request->accountName]);

        // Create the Admin User
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'account_id' => $account->id, // Assign new account ID
            'role_id' => $role->id,
        ]);
        // event(new Registered($user));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
