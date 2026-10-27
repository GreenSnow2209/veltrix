<?php

namespace Veltrix\Grid;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class Model
{
    protected EloquentModel $model;
    protected Builder $builder;
    protected Grid $grid;
    protected int $perPage = 20;
    protected bool $usePaginate = true;
    protected string $perPageName = 'per_page';
    protected string $sortName = '_sort';
    protected ?Closure $collectionCallback = null;
    protected array $queries = [];

    public function __construct(EloquentModel $model, Grid $grid)
    {
        $this->model = $model;
        $this->builder = $model->newQuery();
        $this->grid = $grid;
    }

    public function eloquent(): EloquentModel
    {
        return $this->model;
    }

    public function getQuery(): Builder
    {
        return $this->builder;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function usePaginate(bool $use = true): static
    {
        $this->usePaginate = $use;
        return $this;
    }

    public function collection(Closure $callback): static
    {
        $this->collectionCallback = $callback;
        return $this;
    }

    public function addQuery(string $method, array $arguments = []): static
    {
        $this->queries[] = compact('method', 'arguments');
        return $this;
    }

    protected function applyQueries(): void
    {
        foreach ($this->queries as $query) {
            $this->builder = call_user_func_array([$this->builder, $query['method']], $query['arguments']);
        }
    }

    protected function applySort(): void
    {
        $sort = Request::input($this->sortName);

        if (is_array($sort) && isset($sort['column'])) {
            $column = $sort['column'];
            $type = strtolower($sort['type'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $this->builder->orderBy($column, $type);
        }
    }

    public function buildData(): Collection|LengthAwarePaginator
    {
        $this->applyQueries();
        $this->applySort();

        // Automatic relationship eager loading from registered columns
        foreach ($this->grid->getColumns() as $column) {
            $name = $column->getName();
            if (str_contains($name, '.')) {
                $relation = explode('.', $name)[0];
                if (method_exists($this->model, $relation)) {
                    $this->builder->with($relation);
                }
            }
        }

        $perPage = (int)Request::input($this->perPageName, $this->perPage);

        if ($this->usePaginate) {
            $data = $this->builder->paginate($perPage)->appends(Request::all());
        } else {
            $data = $this->builder->get();
        }

        if ($this->collectionCallback instanceof Closure) {
            $data = call_user_func($this->collectionCallback, $data);
        }

        return $data;
    }

    public function __call(string $method, array $parameters)
    {
        $this->addQuery($method, $parameters);
        return $this;
    }
}
