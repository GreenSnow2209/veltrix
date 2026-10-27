<?php

namespace Veltrix\Grid;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class Grid implements Renderable, Htmlable, \Stringable
{
    protected Model $model;
    protected ?Closure $builder = null;
    protected array $columns = [];
    protected Filter $filter;
    protected array $quickSearchFields = [];
    protected ?Closure $actionsCallback = null;
    protected ?Closure $batchActionsCallback = null;

    protected array $options = [
        'pagination'    => true,
        'filter'        => true,
        'exporter'      => true,
        'actions'       => true,
        'row_selector'  => true,
        'create_button' => true,
    ];

    protected array $perPages = [10, 20, 30, 50, 100];
    protected string $resourcePath = '';
    protected bool $isRendering = false;

    public function __construct(EloquentModel $model, ?Closure $builder = null)
    {
        $this->model = new Model($model, $this);
        $this->filter = new Filter($this->model);
        $this->builder = $builder;
        $this->resourcePath = Request::getPathInfo();

        if ($builder instanceof Closure) {
            call_user_func($builder, $this);
        }
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function filter(?Closure $callback = null): Filter
    {
        if ($callback instanceof Closure) {
            call_user_func($callback, $this->filter);
        }

        return $this->filter;
    }

    public function column(string $name, string $label = ''): Column
    {
        $column = new Column($name, $label);
        $column->setGrid($this);
        $this->columns[$name] = $column;

        return $column;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function quickSearch(...$fields): static
    {
        $this->quickSearchFields = $fields;
        return $this;
    }

    public function paginate(int $perPage = 20): static
    {
        $this->model->setPerPage($perPage);
        return $this;
    }

    public function perPages(array $perPages): static
    {
        $this->perPages = $perPages;
        return $this;
    }

    public function getPerPages(): array
    {
        return $this->perPages;
    }

    public function actions(Closure $callback): static
    {
        $this->actionsCallback = $callback;
        return $this;
    }

    public function batchActions(Closure $callback): static
    {
        $this->batchActionsCallback = $callback;
        return $this;
    }

    public function disableActions(bool $disable = true): static
    {
        $this->options['actions'] = !$disable;
        return $this;
    }

    public function disableBatchActions(bool $disable = true): static
    {
        $this->options['row_selector'] = !$disable;
        return $this;
    }

    public function disableCreateButton(bool $disable = true): static
    {
        $this->options['create_button'] = !$disable;
        return $this;
    }

    public function disableFilter(bool $disable = true): static
    {
        $this->options['filter'] = !$disable;
        return $this;
    }

    public function disableExport(bool $disable = true): static
    {
        $this->options['exporter'] = !$disable;
        return $this;
    }

    public function disablePagination(bool $disable = true): static
    {
        $this->options['pagination'] = !$disable;
        $this->model->usePaginate(!$disable);
        return $this;
    }

    public function disableRowSelector(bool $disable = true): static
    {
        $this->options['row_selector'] = !$disable;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getResourcePath(): string
    {
        return $this->resourcePath;
    }

    protected function applyQuickSearch(): void
    {
        $keyword = Request::input('_search_');
        if (!empty($keyword) && !empty($this->quickSearchFields)) {
            $this->model->getQuery()->where(function ($query) use ($keyword) {
                foreach ($this->quickSearchFields as $index => $field) {
                    if ($index === 0) {
                        $query->where($field, 'like', "%{$keyword}%");
                    } else {
                        $query->orWhere($field, 'like', "%{$keyword}%");
                    }
                }
            });
        }
    }

    public function export(): mixed
    {
        $this->applyQuickSearch();
        $this->filter->apply();

        $exporter = new \Veltrix\Grid\Exporters\CsvExporter($this);
        return $exporter->export();
    }

    public function render(): string
    {
        if ($this->isRendering) {
            return '';
        }

        $this->isRendering = true;

        try {
            if (Request::has('_export_')) {
                return (string)$this->export();
            }

            $this->applyQuickSearch();
            $this->filter->apply();

            $data = $this->model->buildData();

            $rows = [];
            foreach ($data as $item) {
                $rowValues = [];
                foreach ($this->columns as $column) {
                    $rowValues[$column->getName()] = $column->renderValue($item);
                }

                $rows[] = [
                    'model'   => $item,
                    'id'      => $item->getKey(),
                    'columns' => $rowValues,
                ];
            }

            $viewName = view()->exists('veltrix::grid.table') ? 'veltrix::grid.table' : 'admin::grid.table';

            return view($viewName, [
                'columns'      => $this->columns,
                'rows'         => $rows,
                'paginator'    => $data instanceof LengthAwarePaginator ? $data : null,
                'filterHtml'   => $this->filter->render(),
                'options'      => $this->options,
                'resourcePath' => $this->resourcePath,
            ])->render();
        } finally {
            $this->isRendering = false;
        }
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
