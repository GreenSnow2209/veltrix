<?php

namespace Veltrix\Grid;

use Closure;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Column
{
    protected Grid $grid;
    protected string $name;
    protected string $label;
    protected array $displayCallbacks = [];
    protected bool $sortable = false;
    protected ?string $help = null;
    protected array $attributes = [];

    public function __construct(string $name, string $label = '')
    {
        $this->name = $name;
        $this->label = $label ?: ucfirst(str_replace(['.', '_'], ' ', $name));
    }

    public function setGrid(Grid $grid): static
    {
        $this->grid = $grid;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function sortable(): static
    {
        $this->sortable = true;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function help(string $help): static
    {
        $this->help = $help;
        return $this;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function style(string $style): static
    {
        $this->attributes['style'] = $style;
        return $this;
    }

    public function width(int $width): static
    {
        return $this->style("width: {$width}px; max-width: {$width}px;");
    }

    public function color(string $color): static
    {
        return $this->style("color: {$color};");
    }

    public function display(Closure $callback): static
    {
        $this->displayCallbacks[] = $callback;
        return $this;
    }

    public function image(string $server = '', int $width = 40, int $height = 40): static
    {
        return $this->display(function ($value) use ($server, $width, $height) {
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
        return $this->display(function ($value) use ($href, $target) {
            $url = $href ?: $value;
            return "<a href=\"{$url}\" target=\"{$target}\" class=\"vx-link\">{$value}</a>";
        });
    }

    public function badge(string $style = 'primary'): static
    {
        return $this->display(function ($value) use ($style) {
            return "<span class=\"vx-badge vx-badge-{$style}\">{$value}</span>";
        });
    }

    public function label(string $style = 'default'): static
    {
        return $this->display(function ($value) use ($style) {
            return "<span class=\"vx-label vx-label-{$style}\">{$value}</span>";
        });
    }

    public function using(array $values, string $default = ''): static
    {
        return $this->display(function ($value) use ($values, $default) {
            return $values[$value] ?? ($default ?: $value);
        });
    }

    public function bool(array $values = []): static
    {
        return $this->display(function ($value) use ($values) {
            $bool = (bool)$value;
            $icon = $bool
                ? '<span class="vx-text-success"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>'
                : '<span class="vx-text-danger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span>';

            return $icon;
        });
    }

    public function switch(array $states = []): static
    {
        return $this->display(function ($value) use ($states) {
            $checked = (bool)$value ? 'checked' : '';
            $id = $this->getKey();
            $column = $this->getName();
            return "<label class=\"vx-switch\">
                <input type=\"checkbox\" data-id=\"{$id}\" data-column=\"{$column}\" class=\"vx-switch-input\" {$checked} onchange=\"Veltrix.handleSwitch(this)\">
                <span class=\"vx-switch-slider\"></span>
            </label>";
        });
    }

    public function renderValue(EloquentModel $row): mixed
    {
        $value = $this->extractValue($row, $this->name);

        foreach ($this->displayCallbacks as $callback) {
            $boundCallback = $callback->bindTo($row);
            $value = call_user_func($boundCallback, $value, $this);
        }

        if (is_array($value) || $value instanceof \Illuminate\Support\Collection) {
            return $value->implode(', ');
        }

        return $value;
    }

    protected function extractValue(EloquentModel $row, string $name): mixed
    {
        if (str_contains($name, '.')) {
            $parts = explode('.', $name);
            $current = $row;
            foreach ($parts as $part) {
                if ($current === null) {
                    return null;
                }
                if ($current instanceof \Illuminate\Support\Collection) {
                    return $current->pluck($part);
                }
                $current = $current->{$part} ?? null;
            }
            return $current;
        }

        return $row->getAttribute($name) ?? $row->{$name} ?? null;
    }
}
