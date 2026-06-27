<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->status !== 'active') {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda sedang tidak aktif.']);
        }

        abort_unless(in_array($request->user()->role, $roles, true), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}
