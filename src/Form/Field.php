<?php

namespace Veltrix\Form;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;

abstract class Field implements Renderable, Htmlable, \Stringable
{
    protected string $column;
    protected string $label;
    protected mixed $value = null;
    protected mixed $default = null;
    protected mixed $rules = null;
    protected ?string $help = null;
    protected ?string $placeholder = null;
    protected array $attributes = [];
    protected array $options = [];
    protected ?Form $form = null;
    protected ?Model $model = null;

    public function __construct(string $column, string $label = '')
    {
        $this->column = $column;
        $this->label = $label ?: ucfirst(str_replace(['.', '_'], ' ', $column));
    }

    public function setForm(Form $form): static
    {
        $this->form = $form;
        return $this;
    }

    public function setModel(Model $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function rules(mixed $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    public function getRules(): mixed
    {
        return $this->rules;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;
        return $this;
    }

    public function help(string $help): static
    {
        $this->help = $help;
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function required(): static
    {
        $this->rules = $this->rules ? "required|{$this->rules}" : 'required';
        return $this;
    }

    public function attribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function options(mixed $options): static
    {
        if (is_callable($options)) {
            $options = call_user_func($options);
        }

        if ($options instanceof \Illuminate\Contracts\Support\Arrayable) {
            $options = $options->toArray();
        } elseif ($options instanceof \Traversable) {
            $options = iterator_to_array($options);
        }

        $this->options = (array)$options;
        return $this;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): mixed
    {
        if ($this->value !== null) {
            return $this->value;
        }

        if ($this->model) {
            $val = $this->model->getAttribute($this->column);
            if ($val !== null) {
                return $val;
            }
        }

        return $this->default;
    }

    public function prepare(mixed $value): mixed
    {
        return $value;
    }

    abstract public function renderField(): string;

    public function render(): string
    {
        $fieldHtml = $this->renderField();
        $helpHtml = $this->help ? "<div class=\"vx-form-help\">{$this->help}</div>" : '';
        $isRequired = is_string($this->rules) && str_contains($this->rules, 'required');
        $requiredMark = $isRequired ? '<span class=\"vx-text-danger\">*</span>' : '';

        return "<div class=\"vx-form-group\">
            <label class=\"vx-form-label\" for=\"field-{$this->column}\">{$this->label} {$requiredMark}</label>
            <div class=\"vx-form-control-wrap\">
                {$fieldHtml}
                {$helpHtml}
            </div>
        </div>";
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
