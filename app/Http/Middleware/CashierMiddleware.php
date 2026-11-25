<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || ! in_array(Auth::user()->role, ['admin', 'kasir'], true)) {
            return redirect('/login')->with('error', 'Akses kasir diperlukan.');
        }

        return $next($request);
    }
}
