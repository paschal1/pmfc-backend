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
    public function handle($request, Closure $next, $role)
    {
        // Check if the user has the role
        if ($request->user()->role !== $role) {
            // Handle unauthorized access
            return response()->json([
                'error' => [
                    'message' => 'You are not authorized to access this resource.',
                    'status_code' => 401,
                    'help' => 'Please check your credentials or contact support.'
                ]
            ], 401);
            
        }
    
        return $next($request);
    }
    
}
