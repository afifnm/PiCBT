<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('student')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('student.login')
                ->withErrors(['nis' => 'Silakan login terlebih dahulu.']);
        }

        return $next($request);
    }
}
