<?php

namespace Veltrix\Middleware;

use Closure;
use Illuminate\Http\Request;
use Veltrix\Facades\Veltrix;

class Authenticate
{
    /**
     * Handle incoming request for admin authentication.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $guard = config('veltrix.auth.guard', config('admin.auth.guard', 'admin'));
        config(['auth.defaults.guard' => $guard]);

        $redirectTo = veltrix_base_path(config('veltrix.auth.redirect_to', config('admin.auth.redirect_to', 'auth/login')));

        if (Veltrix::guard()->guest() && !$this->shouldPassThrough($request)) {
            if ($request->expectsJson() || $request->is('*/_handle_*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest($redirectTo);
        }

        return $next($request);
    }

    /**
     * Determine if request should pass through without login.
     */
    protected function shouldPassThrough(Request $request): bool
    {
        $excepts = config('veltrix.auth.excepts', config('admin.auth.excepts', []));

        return collect($excepts)
            ->map('veltrix_base_path')
            ->contains(function ($except) use ($request) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                return $request->is($except);
            });
    }
}
