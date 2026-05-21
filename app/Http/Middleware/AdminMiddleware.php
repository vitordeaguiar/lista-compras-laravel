<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, $next)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Acesso negado.');
        }
        return $next($request);
    }
}
