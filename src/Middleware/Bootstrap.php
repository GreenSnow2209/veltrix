<?php

namespace Veltrix\Middleware;

use Closure;
use Illuminate\Http\Request;
use Veltrix\Facades\Veltrix;

class Bootstrap
{
    public function handle(Request $request, Closure $next): mixed
    {
        Veltrix::bootstrap();

        return $next($request);
    }
}
