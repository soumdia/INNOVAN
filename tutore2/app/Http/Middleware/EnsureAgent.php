<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAgent
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        // admin = administrateur général, agent = agent terrain CIE
        if (!in_array(Auth::user()->role, ['admin', 'agent'])) {
            return redirect('/client');
        }

        return $next($request);
    }
}
