<?php

namespace Veltrix\Auth\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'http_method', 'http_path'];

    public static array $httpMethods = [
        'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD',
    ];

    public function __construct(array $attributes = [])
    {
        $connection = config('veltrix.database.connection') ?: config('admin.database.connection') ?: config('database.default');
        $this->setConnection($connection);

        $table = config('veltrix.database.permissions_table') ?: config('admin.database.permissions_table', 'admin_permissions');
        $this->setTable($table);

        parent::__construct($attributes);
    }

    public function roles(): BelongsToMany
    {
        $pivotTable = config('veltrix.database.role_permissions_table', config('admin.database.role_permissions_table', 'admin_role_permissions'));
        $relatedModel = config('veltrix.database.roles_model', config('admin.database.roles_model', Role::class));

        return $this->belongsToMany($relatedModel, $pivotTable, 'permission_id', 'role_id')->withTimestamps();
    }

    /**
     * Determine if request matches this permission rule.
     *
     * @param Request $request
     * @return bool
     */
    public function shouldPassThrough(Request $request): bool
    {
        if (empty($this->http_method) && empty($this->http_path)) {
            return true;
        }

        if ($this->slug === '*' || $this->http_path === '*') {
            return true;
        }

        $defaultMethod = $this->http_method;
        $prefix = trim((string)config('veltrix.route.prefix', config('admin.route.prefix', 'admin')), '/');

        $lines = array_filter(explode("\n", (string)$this->http_path));

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $method = $defaultMethod;

            if (Str::contains($line, ':')) {
                [$methodStr, $pathStr] = explode(':', $line, 2);
                $method = explode(',', $methodStr);
                $line = $pathStr;
            }

            $line = trim($line);

            if ($line === '*' || $line === '/*') {
                $matchPath = '*';
            } elseif ($line === '/') {
                $matchPath = $prefix ?: '/';
            } else {
                $matchPath = ($prefix ? $prefix . '/' : '') . ltrim($line, '/');
            }

            if ($this->matchRequest($matchPath, (array)$method, $request)) {
                return true;
            }
        }

        return false;
    }

    public function getHttpPathAttribute(?string $path): string
    {
        return str_replace("\r\n", "\n", (string)$path);
    }

    protected function matchRequest(string $pattern, array $methods, Request $request): bool
    {
        $pattern = trim($pattern, '/');
        if ($pattern === '') {
            $pattern = '/';
        }

        if (!$request->is($pattern)) {
            return false;
        }

        $methods = array_filter(array_map('strtoupper', array_map('trim', $methods)));

        return empty($methods) || in_array($request->method(), $methods, true);
    }

    public function setHttpMethodAttribute(mixed $method): void
    {
        if (is_array($method)) {
            $this->attributes['http_method'] = implode(',', array_filter($method));
        } else {
            $this->attributes['http_method'] = $method;
        }
    }

    public function getHttpMethodAttribute(mixed $method): array
    {
        if (is_string($method)) {
            return array_filter(explode(',', $method));
        }

        return (array)$method;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }
}
