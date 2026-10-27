<?php

namespace Veltrix;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Veltrix\Auth\Database\Menu;
use Veltrix\Controllers\AuthController;
use Veltrix\Grid\Grid;
use Veltrix\Form\Form;
use Veltrix\Show\Show;
use Veltrix\Tree\Tree;
use Veltrix\Layout\Content;
use Veltrix\Traits\HasAssets;
use Veltrix\Widgets\Navbar;

class Veltrix
{
    use HasAssets;

    public const VERSION = '1.0.0';

    protected ?Navbar $navbar = null;

    protected array $menu = [];

    public static ?string $metaTitle = null;

    public static ?string $favicon = null;

    public static array $extensions = [];

    protected static array $bootingCallbacks = [];

    protected static array $bootedCallbacks = [];

    public static function getLongVersion(): string
    {
        return sprintf('Veltrix <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    public function grid(mixed $model, ?Closure $callable = null): Grid
    {
        return new Grid($this->getModel($model), $callable);
    }

    public function form(mixed $model, ?Closure $callable = null): Form
    {
        return new Form($this->getModel($model), $callable);
    }

    public function show(mixed $model, mixed $callable = null): Show
    {
        return new Show($this->getModel($model), $callable);
    }

    public function tree(mixed $model = null, ?Closure $callable = null): Tree
    {
        return new Tree($model ? $this->getModel($model) : null, $callable);
    }

    public function content(?Closure $callable = null): Content
    {
        return new Content($callable);
    }

    public function getModel(mixed $model): Model
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return new $model();
        }

        throw new InvalidArgumentException("[$model] is not a valid Eloquent model instance or class name.");
    }

    public function menu(): array
    {
        if (!empty($this->menu)) {
            return $this->menu;
        }

        $menuClass = config('veltrix.database.menu_model', config('admin.database.menu_model', Menu::class));

        if (!class_exists($menuClass)) {
            return [];
        }

        /** @var Menu $menuModel */
        $menuModel = new $menuClass();

        return $this->menu = $menuModel->toTree();
    }

    public function menuLinks(array $menu = []): array
    {
        if (empty($menu)) {
            $menu = $this->menu();
        }

        $links = [];

        foreach ($menu as $item) {
            if (!empty($item['children'])) {
                $links = array_merge($links, $this->menuLinks($item['children']));
            } else {
                $links[] = Arr::only($item, ['title', 'uri', 'icon']);
            }
        }

        return $links;
    }

    public static function setTitle(?string $title): void
    {
        self::$metaTitle = $title;
    }

    public function title(): string
    {
        return self::$metaTitle ?: config('veltrix.title', config('admin.title', 'Veltrix Admin'));
    }

    public static function favicon(?string $favicon = null): ?string
    {
        if (is_null($favicon)) {
            return static::$favicon ?: config('veltrix.favicon', config('admin.favicon'));
        }

        return static::$favicon = $favicon;
    }

    public function user(): ?Authenticatable
    {
        return $this->guard()->user();
    }

    public function guard(): Guard|StatefulGuard
    {
        $guard = config('veltrix.auth.guard', config('admin.auth.guard', 'admin'));
        return Auth::guard($guard);
    }

    public function navbar(?Closure $builder = null): Navbar
    {
        if (is_null($builder)) {
            return $this->getNavbar();
        }

        call_user_func($builder, $this->getNavbar());
        return $this->getNavbar();
    }

    public function getNavbar(): Navbar
    {
        if (is_null($this->navbar)) {
            $this->navbar = new Navbar();
        }

        return $this->navbar;
    }

    public function routes(): void
    {
        $prefix = config('veltrix.route.prefix', config('admin.route.prefix', 'admin'));
        $middleware = config('veltrix.route.middleware', config('admin.route.middleware', ['web', 'admin']));
        $domain = config('veltrix.route.domain', config('admin.route.domain'));

        $attributes = [
            'prefix'     => $prefix,
            'middleware' => $middleware,
        ];

        if ($domain) {
            $attributes['domain'] = $domain;
        }

        app('router')->group($attributes, function ($router) {
            $router->namespace('\\Veltrix\\Controllers')->group(function ($router) {
                $router->resource('auth/users', 'UserController')->names('admin.auth.users');
                $router->resource('auth/roles', 'RoleController')->names('admin.auth.roles');
                $router->resource('auth/permissions', 'PermissionController')->names('admin.auth.permissions');
                $router->resource('auth/menu', 'MenuController', ['except' => ['create']])->names('admin.auth.menu');
                $router->resource('auth/logs', 'LogController', ['only' => ['index', 'destroy']])->names('admin.auth.logs');

                $router->post('_handle_form_', 'HandleController@handleForm')->name('admin.handle-form');
                $router->post('_handle_action_', 'HandleController@handleAction')->name('admin.handle-action');
                $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('admin.handle-selectable');
                $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('admin.handle-renderable');
            });

            $authController = config('veltrix.auth.controller', config('admin.auth.controller', AuthController::class));

            $router->get('auth/login', $authController . '@getLogin')->name('admin.login');
            $router->post('auth/login', $authController . '@postLogin');
            $router->get('auth/logout', $authController . '@getLogout')->name('admin.logout');
            $router->get('auth/setting', $authController . '@getSetting')->name('admin.setting');
            $router->put('auth/setting', $authController . '@putSetting');
        });

        // Dedicated streaming asset route for pre-compiled assets
        app('router')->get(
            ($prefix ? trim($prefix, '/') . '/' : '') . '_veltrix_assets_/{path}',
            [\Veltrix\Controllers\AssetController::class, 'serve']
        )->where('path', '.*')->name('veltrix.assets');
    }

    public static function extend(string $name, string $class): void
    {
        static::$extensions[$name] = $class;
    }

    public static function booting(callable $callback): void
    {
        static::$bootingCallbacks[] = $callback;
    }

    public static function booted(callable $callback): void
    {
        static::$bootedCallbacks[] = $callback;
    }

    public function bootstrap(): void
    {
        $this->fireBootingCallbacks();

        $bootstrapFile = config('veltrix.bootstrap', config('admin.bootstrap', veltrix_path('bootstrap.php')));
        if (file_exists($bootstrapFile)) {
            require $bootstrapFile;
        }

        $this->fireBootedCallbacks();
    }

    protected function fireBootingCallbacks(): void
    {
        foreach (static::$bootingCallbacks as $callback) {
            call_user_func($callback);
        }
    }

    protected function fireBootedCallbacks(): void
    {
        foreach (static::$bootedCallbacks as $callback) {
            call_user_func($callback);
        }
    }

    public function disablePjax(): void
    {
        // Safe no-op in modern Veltrix
    }
}
