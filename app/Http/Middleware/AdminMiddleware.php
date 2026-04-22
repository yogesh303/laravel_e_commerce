<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please login first');
        }

        if (Auth::user()->role !== 'admin') {
            return redirect('/products')->with('error', 'Access denied');
        }

        return $next($request);
    }
}
