<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function profileUpdate(Request $request)
    {
        $request->validate(['name' => 'required|max:255']);
        /** @var User $user */
        $user = Auth::user();
        $user->update(['name' => $request->name]);

        return redirect()->route('home')->with('success', 'Profile updated successfully!');
    }

    public function changePassword()
    {
        return view('profile.change-password');
    }

    public function changePasswordUpdate(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validatedData = $request->validate([
            'current_password' => ['required', 'string', 'min:8', function ($attribute, $value, $fail) use ($user) {
                if (! Hash::check($value, $user->password)) {
                    return $fail('The current password is incorrect.');
                }
            }],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' will check if new_password_confirmation is filled
            'new_password_confirmation' => ['required', 'string', 'min:8'],
        ]);

        $user->update(['password' => Hash::make($request->new_password), 'password_changed_at' => now()]);

        return redirect()->route('home')->with('success', 'Password updated successfully.');
    }
}
