<?php

namespace Veltrix\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Veltrix\Auth\Database\OperationLog;
use Veltrix\Facades\Veltrix;

class LogOperation
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->shouldLogOperation($request)) {
            $user = Veltrix::user();

            if ($user) {
                $secretFields = config('veltrix.operation_log.secret_fields', ['password', 'password_confirmation', '_token']);
                $input = $request->except($secretFields);

                $log = [
                    'user_id' => $user->getAuthIdentifier(),
                    'path'    => substr($request->path(), 0, 255),
                    'method'  => $request->method(),
                    'ip'      => $request->ip() ?: '127.0.0.1',
                    'input'   => json_encode($input, JSON_UNESCAPED_UNICODE),
                ];

                try {
                    $logModel = config('veltrix.database.operation_log_model', config('admin.database.operation_log_model', OperationLog::class));
                    $logModel::create($log);
                } catch (\Throwable $e) {
                    // Do not break request execution if logging fails
                }
            }
        }

        return $next($request);
    }

    protected function shouldLogOperation(Request $request): bool
    {
        return config('veltrix.operation_log.enable', config('admin.operation_log.enable', true))
            && !$this->inExceptArray($request)
            && $this->inAllowedMethods($request->method());
    }

    protected function inAllowedMethods(string $method): bool
    {
        $allowed = config('veltrix.operation_log.allowed_methods', ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH']);
        return in_array(strtoupper($method), array_map('strtoupper', $allowed), true);
    }

    protected function inExceptArray(Request $request): bool
    {
        $excepts = config('veltrix.operation_log.except', [
            'auth/logs*',
            '_veltrix_assets_*',
        ]);

        $prefix = trim((string)config('veltrix.route.prefix', config('admin.route.prefix', 'admin')), '/');

        foreach ($excepts as $except) {
            $path = ($prefix ? $prefix . '/' : '') . ltrim($except, '/');

            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }
}
