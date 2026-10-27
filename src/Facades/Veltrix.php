<?php

namespace Veltrix\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Veltrix\Grid\Grid grid(mixed $model, ?\Closure $callable = null)
 * @method static \Veltrix\Form\Form form(mixed $model, ?\Closure $callable = null)
 * @method static \Veltrix\Show\Show show(mixed $model, mixed $callable = null)
 * @method static \Veltrix\Tree\Tree tree(mixed $model = null, ?\Closure $callable = null)
 * @method static \Veltrix\Layout\Content content(?\Closure $callable = null)
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null user()
 * @method static \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard guard()
 * @method static string title()
 * @method static void setTitle(?string $title)
 * @method static void routes()
 * @method static array menu()
 * @method static array menuLinks(array $menu = [])
 * @method static \Veltrix\Widgets\Navbar navbar(?\Closure $builder = null)
 * @method static \Veltrix\Widgets\Navbar getNavbar()
 * @method static array css(mixed $css = null)
 * @method static array js(mixed $js = null)
 * @method static array script(mixed $script = '', bool $deferred = false)
 * @method static array style(mixed $style = '')
 * @method static array html(mixed $html = '')
 * @method static void extend(string $name, string $class)
 * @method static void booting(callable $callback)
 * @method static void booted(callable $callback)
 * @method static void bootstrap()
 *
 * @see \Veltrix\Veltrix
 */
class Veltrix extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'veltrix';
    }
}
