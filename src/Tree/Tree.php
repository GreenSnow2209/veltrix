<?php

namespace Veltrix\Tree;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;

class Tree implements Renderable, Htmlable, \Stringable
{
    protected ?Model $model = null;
    protected ?Closure $branchCallback = null;
    protected bool $useCreate = true;
    protected bool $useSave = true;
    protected bool $useRefresh = true;
    protected bool $isRendering = false;

    public function __construct(?Model $model = null, ?Closure $callback = null)
    {
        $this->model = $model;

        if ($callback instanceof Closure) {
            call_user_func($callback, $this);
        }
    }

    public function branch(Closure $callback): static
    {
        $this->branchCallback = $callback;
        return $this;
    }

    public function disableCreate(bool $disable = true): static
    {
        $this->useCreate = !$disable;
        return $this;
    }

    public function disableSave(bool $disable = true): static
    {
        $this->useSave = !$disable;
        return $this;
    }

    public function disableRefresh(bool $disable = true): static
    {
        $this->useRefresh = !$disable;
        return $this;
    }

    public function getItems(): array
    {
        if (!$this->model) {
            return [];
        }

        return $this->model->toTree();
    }

    public function renderBranch(array $branch): string
    {
        if ($this->branchCallback instanceof Closure) {
            return call_user_func($this->branchCallback, $branch);
        }

        $title = $branch['title'] ?? $branch['name'] ?? 'Item #' . ($branch['id'] ?? '');
        $icon = !empty($branch['icon']) ? "<span class=\"vx-tree-icon\"></span>" : '';
        $uri = !empty($branch['uri']) ? "<span class=\"vx-text-muted vx-text-sm\" style=\"margin-left: 8px;\">/{$branch['uri']}</span>" : '';

        return "{$icon} <span>{$title}</span> {$uri}";
    }

    protected function prepareItemsForView(array $items): array
    {
        return array_map(function ($item) {
            $item['rendered_branch'] = $this->renderBranch($item);
            if (!empty($item['children'])) {
                $item['children'] = $this->prepareItemsForView($item['children']);
            }
            return $item;
        }, $items);
    }

    public function render(): string
    {
        if ($this->isRendering) {
            return '';
        }

        $this->isRendering = true;

        try {
            $items = $this->prepareItemsForView($this->getItems());
            $viewName = view()->exists('veltrix::tree') ? 'veltrix::tree' : 'admin::tree';

            return view($viewName, [
                'items'      => $items,
                'useCreate'  => $this->useCreate,
                'useSave'    => $this->useSave,
                'useRefresh' => $this->useRefresh,
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
