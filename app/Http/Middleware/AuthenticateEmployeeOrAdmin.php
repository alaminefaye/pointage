<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateEmployeeOrAdmin
{
    /**
     * Handle an incoming request.
     * Accepts either admin authentication (auth) or employee session.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as admin
        if (auth()->check()) {
            return $next($request);
        }

        // Check if employee is logged in via session
        if (session()->has('employee_id')) {
            return $next($request);
        }

        // If neither, return unauthenticated response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Redirect to appropriate login page
        if (str_starts_with($request->path(), 'employee')) {
            return redirect()->route('employee.login');
        }

        return redirect()->route('login');
    }
}

