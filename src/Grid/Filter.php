<?php

namespace Veltrix\Grid;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

class Filter implements Renderable
{
    protected Model $model;
    protected array $filters = [];
    protected array $scopes = [];
    protected bool $expand = false;
    protected bool $disableIdFilter = false;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function expand(bool $expand = true): static
    {
        $this->expand = $expand;
        return $this;
    }

    public function isExpanded(): bool
    {
        return $this->expand;
    }

    public function disableIdFilter(bool $disable = true): static
    {
        $this->disableIdFilter = $disable;
        return $this;
    }

    public function like(string $column, string $label = ''): static
    {
        $this->filters[$column] = [
            'type'  => 'like',
            'label' => $label ?: ucfirst(str_replace('_', ' ', $column)),
        ];
        return $this;
    }

    public function equal(string $column, string $label = ''): static
    {
        $this->filters[$column] = [
            'type'  => 'equal',
            'label' => $label ?: ucfirst(str_replace('_', ' ', $column)),
        ];
        return $this;
    }

    public function between(string $column, string $label = ''): static
    {
        $this->filters[$column] = [
            'type'  => 'between',
            'label' => $label ?: ucfirst(str_replace('_', ' ', $column)),
        ];
        return $this;
    }

    public function scope(string $key, string $label = '', ?Closure $callback = null): static
    {
        $this->scopes[$key] = [
            'key'      => $key,
            'label'    => $label ?: ucfirst($key),
            'callback' => $callback,
        ];
        return $this;
    }

    public function apply(): void
    {
        $query = $this->model->getQuery();

        // Check active scope
        $currentScope = Request::input('_scope_');
        if ($currentScope && isset($this->scopes[$currentScope])) {
            $callback = $this->scopes[$currentScope]['callback'];
            if ($callback instanceof Closure) {
                call_user_func($callback, $query);
            } elseif (method_exists($this->model->eloquent(), 'scope' . ucfirst($currentScope))) {
                $query->{$currentScope}();
            }
        }

        foreach ($this->filters as $column => $filter) {
            $val = Request::input($column);

            if ($val === null || $val === '') {
                continue;
            }

            if ($filter['type'] === 'like') {
                $query->where($column, 'like', "%{$val}%");
            } elseif ($filter['type'] === 'equal') {
                $query->where($column, $val);
            } elseif ($filter['type'] === 'between' && is_array($val)) {
                $start = $val['start'] ?? null;
                $end = $val['end'] ?? null;
                if ($start !== null && $end !== null) {
                    $query->whereBetween($column, [$start, $end]);
                } elseif ($start !== null) {
                    $query->where($column, '>=', $start);
                } elseif ($end !== null) {
                    $query->where($column, '<=', $end);
                }
            }
        }
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function render(): string
    {
        $view = view()->exists('veltrix::grid.filter') ? 'veltrix::grid.filter' : 'admin::grid.filter';
        return view($view, [
            'filters'  => $this->filters,
            'scopes'   => $this->scopes,
            'expanded' => $this->expand,
        ])->render();
    }
}
