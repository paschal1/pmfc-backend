<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$rolesOrPermissions): Response
    {

        if (!auth()->check()) {
            abort(403);
        }
    
        if (!auth()->user()->hasAnyRole($rolesOrPermissions) && !auth()->user()->hasAnyPermission($rolesOrPermissions)) {
            abort(403);
        }

        return $next($request);


    }
}
