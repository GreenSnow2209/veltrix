<?php

namespace Veltrix\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

trait ModelTree
{
    protected static array $branchOrder = [];

    protected string $parentColumn = 'parent_id';

    protected string $titleColumn = 'title';

    protected string $orderColumn = 'order';

    protected ?\Closure $queryCallback = null;

    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->parentColumn);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->parentColumn);
    }

    public function getParentColumn(): string
    {
        return $this->parentColumn;
    }

    public function setParentColumn(string $column): void
    {
        $this->parentColumn = $column;
    }

    public function getTitleColumn(): string
    {
        return $this->titleColumn;
    }

    public function setTitleColumn(string $column): void
    {
        $this->titleColumn = $column;
    }

    public function getOrderColumn(): string
    {
        return $this->orderColumn;
    }

    public function setOrderColumn(string $column): void
    {
        $this->orderColumn = $column;
    }

    public function withQuery(?\Closure $query = null): static
    {
        $this->queryCallback = $query;
        return $this;
    }

    public function toTree(): array
    {
        return $this->buildNestedArray();
    }

    protected function buildNestedArray(array $nodes = [], mixed $parentId = 0): array
    {
        $branch = [];

        if (empty($nodes)) {
            $nodes = $this->allNodes();
        }

        foreach ($nodes as $node) {
            if ($node[$this->parentColumn] == $parentId) {
                $children = $this->buildNestedArray($nodes, $node[$this->getKeyName()]);
                if ($children) {
                    $node['children'] = $children;
                }
                $branch[] = $node;
            }
        }

        return $branch;
    }

    public function allNodes(): array
    {
        $self = new static();

        if ($this->queryCallback instanceof \Closure) {
            $self = call_user_func($this->queryCallback, $self);
        }

        return $self->orderBy($this->orderColumn, 'asc')->get()->toArray();
    }

    protected static function setBranchOrder(array $order): void
    {
        static::$branchOrder = array_flip(Arr::flatten($order));
        static::$branchOrder = array_map(function ($item) {
            return ++$item;
        }, static::$branchOrder);
    }

    public static function saveOrder(array $tree = [], mixed $parentId = 0): void
    {
        if (empty(static::$branchOrder)) {
            static::setBranchOrder($tree);
        }

        foreach ($tree as $branch) {
            if (!isset($branch['id'])) {
                continue;
            }

            $node = static::find($branch['id']);
            if (!$node) {
                continue;
            }

            $node->{$node->getParentColumn()} = $parentId;
            if (isset(static::$branchOrder[$branch['id']])) {
                $node->{$node->getOrderColumn()} = static::$branchOrder[$branch['id']];
            }
            $node->save();

            if (isset($branch['children']) && is_array($branch['children'])) {
                static::saveOrder($branch['children'], $branch['id']);
            }
        }
    }

    public static function selectOptions(?\Closure $closure = null, string $rootText = 'ROOT'): array
    {
        $options = (new static())->withQuery($closure)->buildSelectOptions();
        return collect($options)->prepend($rootText, 0)->all();
    }

    protected function buildSelectOptions(array $nodes = [], mixed $parentId = 0, string $prefix = '', string $space = '&nbsp;'): array
    {
        $prefix = $prefix ?: '┝' . $space;
        $options = [];

        if (empty($nodes)) {
            $nodes = $this->allNodes();
        }

        foreach ($nodes as $node) {
            if ($node[$this->parentColumn] == $parentId) {
                $nodeTitle = $prefix . $space . $node[$this->titleColumn];
                $childrenPrefix = str_replace('┝', str_repeat($space, 6), $prefix) . '┝' . str_replace(['┝', $space], '', $prefix);

                $options[$node[$this->getKeyName()]] = $nodeTitle;
                $children = $this->buildSelectOptions($nodes, $node[$this->getKeyName()], $childrenPrefix);
                if ($children) {
                    $options += $children;
                }
            }
        }

        return $options;
    }

    public function delete()
    {
        $this->where($this->parentColumn, $this->getKey())->delete();
        return parent::delete();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Model $branch) {
            $parentColumn = $branch->getParentColumn();

            if (Request::has($parentColumn) && Request::input($parentColumn) == $branch->getKey()) {
                throw new \Exception(veltrix_trans('parent_select_error') ?: 'Cannot select self as parent');
            }

            if (Request::has('_order')) {
                $order = Request::input('_order');
                Request::offsetUnset('_order');
                static::saveOrder($order);
                return false;
            }

            return $branch;
        });
    }
}
