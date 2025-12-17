<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class plan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant()->data['plan'] === 'free' && Product::count() <= 5) {
            return response()->json(['error' => 'User limit reached'], 403);
        }
        if (tenant()->data['plan'] === 'basic' && Product::count() <= 20) {
            return response()->json(['error' => 'User limit reached'], 403);
        }
        if (tenant()->data['plan'] === 'premium' && Product::count() <= 50) {
            return response()->json(['error' => 'User limit reached'], 403);
        }
        if (tenant()->data['plan'] === 'enterprise' && Product::count() <= 100) {
            return response()->json(['error' => 'User limit reached'], 403);
        }

        return $next($request);
    }
}
