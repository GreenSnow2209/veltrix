<?php

namespace Veltrix\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Veltrix\Auth\Permission as Checker;
use Veltrix\Facades\Veltrix;

class Permission
{
    protected string $middlewarePrefix = 'admin.permission:';

    public function handle(Request $request, Closure $next, ...$args): mixed
    {
        if (config('veltrix.check_route_permission', config('admin.check_route_permission', true)) === false) {
            return $next($request);
        }

        if (!Veltrix::user() || !empty($args) || $this->shouldPassThrough($request)) {
            return $next($request);
        }

        if (Checker::isAdministrator()) {
            return $next($request);
        }

        if ($this->checkRouteMiddlewarePermission($request)) {
            return $next($request);
        }

        // Check user permissions against current request
        $hasPermission = Veltrix::user()->allPermissions()->first(function ($permission) use ($request) {
            return $permission->shouldPassThrough($request);
        });

        if (!$hasPermission) {
            Checker::error();
        }

        return $next($request);
    }

    protected function checkRouteMiddlewarePermission(Request $request): bool
    {
        $route = $request->route();
        if (!$route) {
            return false;
        }

        $middleware = collect($route->middleware())->first(function ($item) {
            return is_string($item) && Str::startsWith($item, $this->middlewarePrefix);
        });

        if (!$middleware) {
            return false;
        }

        $args = explode(',', str_replace($this->middlewarePrefix, '', $middleware));
        $method = array_shift($args);

        if (!method_exists(Checker::class, $method)) {
            throw new \InvalidArgumentException("Invalid permission check method [$method].");
        }

        call_user_func_array([Checker::class, $method], [$args]);

        return true;
    }

    protected function shouldPassThrough(Request $request): bool
    {
        $excepts = array_merge(
            config('veltrix.auth.excepts', config('admin.auth.excepts', [])),
            [
                'auth/login',
                'auth/logout',
            ]
        );

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
