<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // If not logged in or not an admin, block access
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403);
        }

        return $next($request);
    }
}
