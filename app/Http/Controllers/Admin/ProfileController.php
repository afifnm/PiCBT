<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'username'     => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['nullable', 'string'],
            'new_password'     => ['nullable', 'string', 'min:6'],
        ]);

        if ($request->filled('new_password')) {
            if (! $request->filled('current_password') || ! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak sesuai.'])->withInput();
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->name     = $data['name'];
        $user->username = $data['username'];
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
