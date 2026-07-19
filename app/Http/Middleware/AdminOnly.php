<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Halaman ini khusus admin.');
        }

        return $next($request);
    }
}