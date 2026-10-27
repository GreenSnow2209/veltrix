<?php

namespace Veltrix;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Veltrix\Layout\Content;

class VeltrixServiceProvider extends ServiceProvider
{
    protected array $commands = [
        Console\InstallCommand::class,
        Console\PublishCommand::class,
        Console\MakeCommand::class,
        Console\ControllerCommand::class,
        Console\CreateUserCommand::class,
        Console\ResetPasswordCommand::class,
        Console\MenuCommand::class,
        Console\GenerateMenuCommand::class,
        Console\PermissionCommand::class,
    ];

    protected array $routeMiddleware = [
        'admin.auth'       => Middleware\Authenticate::class,
        'admin.permission' => Middleware\Permission::class,
        'admin.log'        => Middleware\LogOperation::class,
        'admin.bootstrap'  => Middleware\Bootstrap::class,
        'admin.pjax'       => Middleware\Pjax::class,
    ];

    protected array $middlewareGroups = [
        'admin' => [
            'admin.auth',
            'admin.log',
            'admin.bootstrap',
            'admin.permission',
        ],
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/veltrix.php', 'veltrix');
        $this->mergeConfigFrom(__DIR__ . '/../config/veltrix.php', 'admin');

        $this->app->singleton('veltrix', function ($app) {
            return new Veltrix();
        });

        $this->app->alias('veltrix', Veltrix::class);

        $this->loadAdminAuthConfig();
        $this->registerRouteMiddleware();
        $this->macroRouter();

        $this->commands($this->commands);
    }

    public function boot(): void
    {
        // Load views under both 'veltrix' and 'admin' namespaces for complete compatibility
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'veltrix');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'admin');

        // Load translations under both namespaces
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'veltrix');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'admin');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->ensureHttps();

        // Load user defined admin routes if file exists
        $routes = veltrix_path('routes.php');
        if (file_exists($routes)) {
            $this->loadRoutesFrom($routes);
        }

        $this->registerPublishing();

        // Register custom Blade directives
        Blade::directive('box', function ($expression) {
            return "<?php echo (new \\Veltrix\\Widgets\\Box({$expression}))->render(); ?>";
        });
    }

    protected function loadAdminAuthConfig(): void
    {
        $authConfig = config('veltrix.auth', config('admin.auth', []));
        config(Arr::dot($authConfig, 'auth.'));
    }

    protected function registerRouteMiddleware(): void
    {
        $router = $this->app['router'];

        foreach ($this->routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }

        foreach ($this->middlewareGroups as $key => $middlewares) {
            $router->middlewareGroup($key, $middlewares);
        }
    }

    protected function macroRouter(): void
    {
        Router::macro('content', function ($uri, $content, $options = []) {
            /** @var Router $this */
            return $this->match(['GET', 'HEAD'], $uri, function (Content $layout) use ($content, $options) {
                return $layout
                    ->title(Arr::get($options, 'title', ' '))
                    ->description(Arr::get($options, 'desc', ' '))
                    ->body($content);
            });
        });
    }

    protected function ensureHttps(): void
    {
        $prefix = trim((string)config('veltrix.route.prefix', config('admin.route.prefix', 'admin')), '/');
        $isAdmin = str_starts_with(request()->getRequestUri(), '/' . $prefix);

        if ((config('veltrix.https', config('admin.https', false)) || config('veltrix.secure', false)) && $isAdmin) {
            url()->forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/veltrix.php' => config_path('veltrix.php'),
            ], 'veltrix-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/veltrix'),
            ], 'veltrix-views');

            $this->publishes([
                __DIR__ . '/../resources/dist' => public_path('vendor/veltrix'),
            ], 'veltrix-assets');

            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/veltrix'),
            ], 'veltrix-lang');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'veltrix-migrations');

            // Legacy publish tags for Encore\Admin compatibility
            $this->publishes([
                __DIR__ . '/../config/veltrix.php' => config_path('admin.php'),
            ], 'laravel-admin-config');

            $this->publishes([
                __DIR__ . '/../resources/dist' => public_path('vendor/laravel-admin'),
            ], 'laravel-admin-assets');
        }
    }
}
