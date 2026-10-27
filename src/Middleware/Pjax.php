<?php

namespace Veltrix\Middleware;

use Closure;
use Illuminate\Http\Request;

class Pjax
{
    public function handle(Request $request, Closure $next): mixed
    {
        return $next($request);
    }
}
