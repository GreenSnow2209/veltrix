<?php

namespace Veltrix\Show;

use Closure;
use Illuminate\Database\Eloquent\Model;

class Field
{
    protected string $name;
    protected string $label;
    protected ?Closure $asCallback = null;
    protected bool $escape = true;

    public function __construct(string $name, string $label = '')
    {
        $this->name = $name;
        $this->label = $label ?: ucfirst(str_replace(['.', '_'], ' ', $name));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function as(Closure $callback): static
    {
        $this->asCallback = $callback;
        return $this;
    }

    public function unescape(): static
    {
        $this->escape = false;
        return $this;
    }

    public function image(string $server = '', int $width = 120, int $height = 120): static
    {
        $this->escape = false;
        return $this->as(function ($value) use ($server, $width, $height) {
            if (empty($value)) {
                return '';
            }
            $src = $server ? rtrim($server, '/') . '/' . ltrim($value, '/') : $value;
            if (!url()->isValidUrl($src)) {
                $src = veltrix_asset($src);
            }
            return "<img src=\"{$src}\" style=\"width: {$width}px; height: {$height}px; object-fit: cover; border-radius: 6px; border: 1px solid var(--vx-border);\" />";
        });
    }

    public function link(string $href = '', string $target = '_blank'): static
    {
        $this->escape = false;
        return $this->as(function ($value) use ($href, $target) {
            $url = $href ?: $value;
            return "<a href=\"{$url}\" target=\"{$target}\" class=\"vx-link\">{$value}</a>";
        });
    }

    public function badge(string $style = 'primary'): static
    {
        $this->escape = false;
        return $this->as(function ($value) use ($style) {
            return "<span class=\"vx-badge vx-badge-{$style}\">{$value}</span>";
        });
    }

    public function using(array $map, string $default = ''): static
    {
        return $this->as(function ($value) use ($map, $default) {
            return $map[$value] ?? ($default ?: $value);
        });
    }

    public function renderValue(Model $model): mixed
    {
        $val = $model->getAttribute($this->name) ?? $model->{$this->name} ?? null;

        if ($this->asCallback instanceof Closure) {
            $bound = $this->asCallback->bindTo($model);
            $val = call_user_func($bound, $val);
        }

        if (is_array($val) || $val instanceof \Illuminate\Support\Collection) {
            $val = implode(', ', (array)$val);
        }

        if ($this->escape && is_string($val)) {
            return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        }

        return $val;
    }
}
