<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PartnerProfile;
use App\Models\User;
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'role' => ['required', 'in:customer,mitra'],
            'business_name' => ['required_if:role,mitra', 'nullable', 'string', 'max:180'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.regex' => 'Format nomor telepon tidak valid.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        if ($user->role === 'mitra') {
            PartnerProfile::create([
                'user_id' => $user->id,
                'business_name' => $request->business_name,
                'owner_name' => $user->name,
                'phone' => $user->phone,
                'address' => '-',
                'city' => 'Belum diatur',
                'province' => 'Belum diatur',
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route($user->role.'.dashboard');
    }
}
