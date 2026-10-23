<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        if ($user->status !== 'active') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        // Admin always has access to all routes
        if ($user->role === 'admin') {
            return $next($request);
        }

        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
