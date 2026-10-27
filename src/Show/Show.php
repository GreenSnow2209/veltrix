<?php

namespace Veltrix\Show;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;

class Show implements Renderable, Htmlable, \Stringable
{
    protected Model $model;
    protected array $fields = [];
    protected array $relations = [];
    protected bool $isRendering = false;

    public function __construct(Model $model, ?Closure $builder = null)
    {
        $this->model = $model;

        if ($builder instanceof Closure) {
            call_user_func($builder, $this);
        }
    }

    public function field(string $name, string $label = ''): Field
    {
        $field = new Field($name, $label);
        $this->fields[$name] = $field;
        return $field;
    }

    public function divider(): static
    {
        $this->fields['__divider_' . count($this->fields)] = new class('divider') extends Field {
            public function renderValue(Model $model): string
            {
                return '<hr class="vx-divider">';
            }
        };
        return $this;
    }

    public function relation(string $name, Closure $callback): static
    {
        $this->relations[$name] = $callback;
        return $this;
    }

    public function render(): string
    {
        if ($this->isRendering) {
            return '';
        }

        $this->isRendering = true;

        try {
            $renderedFields = [];
            foreach ($this->fields as $field) {
                $renderedFields[] = [
                    'name'  => $field->getName(),
                    'label' => $field->getLabel(),
                    'value' => $field->renderValue($this->model),
                ];
            }

            $viewName = view()->exists('veltrix::show') ? 'veltrix::show' : 'admin::show';

            return view($viewName, [
                'model'     => $this->model,
                'fields'    => $renderedFields,
                'relations' => $this->relations,
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
