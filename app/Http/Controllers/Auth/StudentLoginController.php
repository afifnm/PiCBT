<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentLoginController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nis'      => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('student')->attempt(
            ['nis' => $credentials['nis'], 'password' => $credentials['password']],
            false
        )) {
            $request->session()->regenerate();
            return redirect()->intended(route('student.dashboard'));
        }

        return back()
            ->withInput($request->only('nis'))
            ->withErrors(['nis' => 'NIS atau password salah.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
