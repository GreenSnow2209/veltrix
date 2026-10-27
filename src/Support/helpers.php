<?php

use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;

if (!function_exists('veltrix_path')) {
    /**
     * Get admin install/controllers directory path.
     *
     * @param string $path
     * @return string
     */
    function veltrix_path(string $path = ''): string
    {
        $dir = config('veltrix.directory', config('admin.directory', app_path('Admin')));
        return $dir . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('admin_path')) {
    /**
     * Legacy alias for veltrix_path.
     *
     * @param string $path
     * @return string
     */
    function admin_path(string $path = ''): string
    {
        return veltrix_path($path);
    }
}

if (!function_exists('veltrix_base_path')) {
    /**
     * Get admin base path prefix.
     *
     * @param string $path
     * @return string
     */
    function veltrix_base_path(string $path = ''): string
    {
        $prefix = '/' . trim(config('veltrix.route.prefix', config('admin.route.prefix', 'admin')), '/');
        $prefix = ($prefix === '/') ? '' : $prefix;
        $path = trim($path, '/');

        if ($path === '') {
            return $prefix ?: '/';
        }

        return $prefix . '/' . $path;
    }
}

if (!function_exists('admin_base_path')) {
    /**
     * Legacy alias for veltrix_base_path.
     *
     * @param string $path
     * @return string
     */
    function admin_base_path(string $path = ''): string
    {
        return veltrix_base_path($path);
    }
}

if (!function_exists('veltrix_url')) {
    /**
     * Get fully qualified admin url.
     *
     * @param string $path
     * @param mixed $parameters
     * @param bool|null $secure
     * @return string
     */
    function veltrix_url(string $path = '', mixed $parameters = [], ?bool $secure = null): string
    {
        if (\Illuminate\Support\Facades\URL::isValidUrl($path)) {
            return $path;
        }

        $secure = $secure ?? (config('veltrix.https', config('admin.https', false)) || config('veltrix.secure', config('admin.secure', false)));

        return url(veltrix_base_path($path), $parameters, $secure);
    }
}

if (!function_exists('admin_url')) {
    /**
     * Legacy alias for veltrix_url.
     *
     * @param string $path
     * @param mixed $parameters
     * @param bool|null $secure
     * @return string
     */
    function admin_url(string $path = '', mixed $parameters = [], ?bool $secure = null): string
    {
        return veltrix_url($path, $parameters, $secure);
    }
}

if (!function_exists('veltrix_toastr')) {
    /**
     * Flash a toast notification message to session.
     *
     * @param string $message
     * @param string $type
     * @param array $options
     * @return void
     */
    function veltrix_toastr(string $message = '', string $type = 'success', array $options = []): void
    {
        $toastr = new MessageBag(compact('message', 'type', 'options'));
        session()->flash('toastr', $toastr);
    }
}

if (!function_exists('veltrix_user')) {
    function veltrix_user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return \Veltrix\Facades\Veltrix::user();
    }
}

if (!function_exists('admin_user')) {
    function admin_user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return veltrix_user();
    }
}

if (!function_exists('admin_toastr')) {
    function admin_toastr(string $message = '', string $type = 'success', array $options = []): void
    {
        veltrix_toastr($message, $type, $options);
    }
}

if (!function_exists('veltrix_info')) {
    function veltrix_info(string $title, string $message = '', string $type = 'info'): void
    {
        $bag = new MessageBag(compact('title', 'message', 'type'));
        session()->flash($type, $bag);
    }
}

if (!function_exists('admin_info')) {
    function admin_info(string $title, string $message = '', string $type = 'info'): void
    {
        veltrix_info($title, $message, $type);
    }
}

if (!function_exists('veltrix_success')) {
    function veltrix_success(string $title, string $message = ''): void
    {
        veltrix_info($title, $message, 'success');
    }
}

if (!function_exists('admin_success')) {
    function admin_success(string $title, string $message = ''): void
    {
        veltrix_success($title, $message);
    }
}

if (!function_exists('veltrix_error')) {
    function veltrix_error(string $title, string $message = ''): void
    {
        veltrix_info($title, $message, 'error');
    }
}

if (!function_exists('admin_error')) {
    function admin_error(string $title, string $message = ''): void
    {
        veltrix_error($title, $message);
    }
}

if (!function_exists('veltrix_warning')) {
    function veltrix_warning(string $title, string $message = ''): void
    {
        veltrix_info($title, $message, 'warning');
    }
}

if (!function_exists('admin_warning')) {
    function admin_warning(string $title, string $message = ''): void
    {
        veltrix_warning($title, $message);
    }
}

if (!function_exists('veltrix_asset')) {
    /**
     * Generate an asset URL for package assets.
     *
     * @param string $path
     * @return string
     */
    function veltrix_asset(string $path): string
    {
        $path = ltrim($path, '/');
        $secure = config('veltrix.https', config('admin.https', false));

        // If published under vendor/veltrix or vendor/laravel-admin
        if (file_exists(public_path('vendor/veltrix/' . $path))) {
            return $secure ? secure_asset('vendor/veltrix/' . $path) : asset('vendor/veltrix/' . $path);
        }

        // Fallback to internal route if not published
        $route = veltrix_base_path('_veltrix_assets_/' . $path);
        return $secure ? secure_url($route) : url($route);
    }
}

if (!function_exists('admin_asset')) {
    function admin_asset(string $path): string
    {
        return veltrix_asset($path);
    }
}

if (!function_exists('veltrix_trans')) {
    /**
     * Translate admin string.
     *
     * @param string|null $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    function veltrix_trans(?string $key = null, array $replace = [], ?string $locale = null): string
    {
        if (is_null($key)) {
            return '';
        }

        $subKey = str_contains($key, '.') ? explode('.', $key, 2)[1] : $key;

        $candidates = [
            "veltrix::veltrix.{$subKey}",
            "veltrix::admin.{$subKey}",
            "admin::admin.{$subKey}",
            "admin::veltrix.{$subKey}",
            "veltrix.{$subKey}",
            "admin.{$subKey}",
            "veltrix::{$key}",
            "admin::{$key}",
            $key,
        ];

        foreach ($candidates as $candidate) {
            if (\Illuminate\Support\Facades\Lang::has($candidate, $locale)) {
                $line = __($candidate, $replace, $locale);
                if (is_string($line)) {
                    return $line;
                }
            }
        }

        $line = __($key, $replace, $locale);
        return is_string($line) ? $line : $key;
    }
}

if (!function_exists('admin_trans')) {
    function admin_trans(?string $key = null, array $replace = [], ?string $locale = null): string
    {
        return veltrix_trans($key, $replace, $locale);
    }
}

if (!function_exists('array_delete')) {
    /**
     * Delete from array by value.
     *
     * @param array $array
     * @param mixed $value
     * @return void
     */
    function array_delete(array &$array, mixed $value): void
    {
        $value = Arr::wrap($value);
        foreach ($array as $index => $item) {
            if (in_array($item, $value, true)) {
                unset($array[$index]);
            }
        }
    }
}

if (!function_exists('class_uses_deep')) {
    /**
     * Get all traits including those used by parent classes and other traits.
     *
     * @param object|string $class
     * @param bool $autoload
     * @return array
     */
    function class_uses_deep(object|string $class, bool $autoload = true): array
    {
        $traits = [];
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }
}

if (!function_exists('admin_dump')) {
    function admin_dump(...$vars): string
    {
        ob_start();
        dump(...$vars);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents ?: '';
    }
}

if (!function_exists('veltrix_dump')) {
    function veltrix_dump(...$vars): string
    {
        return admin_dump(...$vars);
    }
}

if (!function_exists('file_size')) {
    function file_size(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        if ($bytes > 1) {
            return $bytes . ' bytes';
        }
        if ($bytes === 1) {
            return '1 byte';
        }
        return '0 bytes';
    }
}

if (!function_exists('prepare_options')) {
    function prepare_options(array $options): array
    {
        $original = [];
        $toReplace = [];

        foreach ($options as $key => &$value) {
            if (is_array($value)) {
                $subArray = prepare_options($value);
                $value = $subArray['options'];
                $original = array_merge($original, $subArray['original']);
                $toReplace = array_merge($toReplace, $subArray['toReplace']);
            } elseif (is_string($value) && str_starts_with($value, 'function(')) {
                $original[] = $value;
                $value = "%{$key}%";
                $toReplace[] = "\"{$value}\"";
            }
        }

        return compact('original', 'toReplace', 'options');
    }
}

if (!function_exists('json_encode_options')) {
    function json_encode_options(array $options): string
    {
        $data = prepare_options($options);
        $json = json_encode($data['options'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return str_replace($data['toReplace'], $data['original'], (string)$json);
    }
}

if (!function_exists('admin_get_route')) {
    function admin_get_route(string $name): string
    {
        $prefix = config('veltrix.route.prefix', config('admin.route.prefix', 'admin'));
        return $prefix . '.' . $name;
    }
}

if (!function_exists('veltrix_get_route')) {
    function veltrix_get_route(string $name): string
    {
        return admin_get_route($name);
    }
}
