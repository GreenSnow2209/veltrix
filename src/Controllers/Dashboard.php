<?php

namespace Veltrix\Controllers;

use Illuminate\Support\Arr;
use Veltrix\Facades\Veltrix;

class Dashboard
{
    public static function title()
    {
        $view = view()->exists('veltrix::dashboard.title') ? 'veltrix::dashboard.title' : 'admin::dashboard.title';
        return view($view);
    }

    public static function environment()
    {
        $envs = [
            ['name' => 'PHP version',       'value' => 'PHP/' . PHP_VERSION],
            ['name' => 'Laravel version',   'value' => app()->version()],
            ['name' => 'Veltrix version',   'value' => \Veltrix\Veltrix::VERSION],
            ['name' => 'CGI',               'value' => php_sapi_name()],
            ['name' => 'OS',                'value' => php_uname('s') . ' ' . php_uname('r')],
            ['name' => 'Cache driver',      'value' => (string)config('cache.default')],
            ['name' => 'Session driver',    'value' => (string)config('session.driver')],
            ['name' => 'Queue driver',      'value' => (string)config('queue.default')],
            ['name' => 'Timezone',          'value' => (string)config('app.timezone')],
            ['name' => 'Locale',            'value' => (string)config('app.locale')],
            ['name' => 'Environment',       'value' => (string)config('app.env')],
        ];

        $view = view()->exists('veltrix::dashboard.environment') ? 'veltrix::dashboard.environment' : 'admin::dashboard.environment';
        return view($view, compact('envs'));
    }

    public static function extensions()
    {
        $extensions = Veltrix::$extensions;
        $view = view()->exists('veltrix::dashboard.extensions') ? 'veltrix::dashboard.extensions' : 'admin::dashboard.extensions';
        return view($view, compact('extensions'));
    }

    public static function dependencies()
    {
        $composerLock = base_path('composer.lock');
        $dependencies = [];

        if (file_exists($composerLock)) {
            $json = json_decode(file_get_contents($composerLock), true);
            $packages = $json['packages'] ?? [];
            foreach ($packages as $pkg) {
                $dependencies[] = [
                    'name'    => $pkg['name'],
                    'version' => $pkg['version'],
                ];
            }
        }

        $view = view()->exists('veltrix::dashboard.dependencies') ? 'veltrix::dashboard.dependencies' : 'admin::dashboard.dependencies';
        return view($view, compact('dependencies'));
    }
}
