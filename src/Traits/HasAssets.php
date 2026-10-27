<?php

namespace Veltrix\Traits;

use Illuminate\Support\Collection;

trait HasAssets
{
    public static array $script = [];
    public static array $deferredScript = [];
    public static array $style = [];
    public static array $css = [];
    public static array $js = [];
    public static array $html = [];
    public static array $headerJs = [];

    /**
     * Add CSS or retrieve all registered CSS files.
     *
     * @param string|array|null $css
     * @return array
     */
    public static function css(mixed $css = null): array
    {
        if (!is_null($css)) {
            static::$css = array_values(array_unique(array_merge(static::$css, (array)$css)));
            return static::$css;
        }

        $baseCss = [
            veltrix_asset('css/veltrix.css'),
        ];

        return array_values(array_unique(array_merge($baseCss, static::$css)));
    }

    /**
     * Add JS or retrieve all registered JS files.
     *
     * @param string|array|null $js
     * @return array
     */
    public static function js(mixed $js = null): array
    {
        if (!is_null($js)) {
            static::$js = array_values(array_unique(array_merge(static::$js, (array)$js)));
            return static::$js;
        }

        $baseJs = [
            veltrix_asset('js/veltrix.js'),
        ];

        return array_values(array_unique(array_merge($baseJs, static::$js)));
    }

    public static function headerJs(mixed $js = null): array
    {
        if (!is_null($js)) {
            static::$headerJs = array_values(array_unique(array_merge(static::$headerJs, (array)$js)));
            return static::$headerJs;
        }

        return static::$headerJs;
    }

    /**
     * Add inline script or retrieve all collected scripts.
     *
     * @param string|array $script
     * @param bool $deferred
     * @return array
     */
    public static function script(mixed $script = '', bool $deferred = false): array
    {
        if (!empty($script)) {
            if ($deferred) {
                static::$deferredScript = array_merge(static::$deferredScript, (array)$script);
                return static::$deferredScript;
            }
            static::$script = array_merge(static::$script, (array)$script);
            return static::$script;
        }

        return array_values(array_unique(array_merge(static::$script, static::$deferredScript)));
    }

    /**
     * Add inline style or retrieve all collected styles.
     *
     * @param string|array $style
     * @return array
     */
    public static function style(mixed $style = ''): array
    {
        if (!empty($style)) {
            static::$style = array_merge(static::$style, (array)$style);
            return static::$style;
        }

        return array_values(array_unique(static::$style));
    }

    /**
     * Add raw HTML snippet to head or footer.
     *
     * @param string|array $html
     * @return array
     */
    public static function html(mixed $html = ''): array
    {
        if (!empty($html)) {
            static::$html = array_merge(static::$html, (array)$html);
            return static::$html;
        }

        return static::$html;
    }
}
