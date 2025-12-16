<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API and JSON requests we don't redirect to a login page,
        // instead we return JSON 401 via the exception handler.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // For web/admin requests, redirect to the admin login route.
        return route('admin.login');
    }
}
