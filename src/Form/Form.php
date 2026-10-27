<?php

namespace Veltrix\Form;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Veltrix\Form\Field;

class Form implements Renderable, Htmlable, \Stringable
{
    protected Model $model;
    protected ?Closure $builder = null;
    protected array $fields = [];
    protected array $ignored = [];
    protected array $savingCallbacks = [];
    protected array $savedCallbacks = [];
    protected array $deletingCallbacks = [];
    protected array $deletedCallbacks = [];
    protected ?string $action = null;
    protected bool $isEditing = false;
    protected bool $isRendering = false;
    protected array $inputs = [];

    public function __construct(Model $model, ?Closure $builder = null)
    {
        $this->model = $model;
        $this->builder = $builder;

        if ($builder instanceof Closure) {
            call_user_func($builder, $this);
        }
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function isEditing(): bool
    {
        return $this->isEditing;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getAction(): string
    {
        if ($this->action) {
            return $this->action;
        }

        $path = rtrim(request()->getPathInfo(), '/');

        if ($this->isEditing) {
            // Remove /edit suffix if present
            if (str_ends_with($path, '/edit')) {
                $path = substr($path, 0, -5);
            }
            return $path;
        }

        // Create mode: POST to resource index
        if (str_ends_with($path, '/create')) {
            $path = substr($path, 0, -7);
        }

        return $path ?: '/';
    }

    public function ignore(string|array $columns): static
    {
        $this->ignored = array_merge($this->ignored, (array)$columns);
        return $this;
    }

    public function saving(Closure $callback): static
    {
        $this->savingCallbacks[] = $callback;
        return $this;
    }

    public function saved(Closure $callback): static
    {
        $this->savedCallbacks[] = $callback;
        return $this;
    }

    public function deleting(Closure $callback): static
    {
        $this->deletingCallbacks[] = $callback;
        return $this;
    }

    public function deleted(Closure $callback): static
    {
        $this->deletedCallbacks[] = $callback;
        return $this;
    }

    public function addField(Field $field): Field
    {
        $field->setForm($this);
        $field->setModel($this->model);
        $this->fields[$field->getColumn()] = $field;
        return $field;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function text(string $column, string $label = ''): Field\Text
    {
        return $this->addField(new Field\Text($column, $label));
    }

    public function password(string $column, string $label = ''): Field\Password
    {
        return $this->addField(new Field\Password($column, $label));
    }

    public function email(string $column, string $label = ''): Field\Email
    {
        return $this->addField(new Field\Email($column, $label));
    }

    public function number(string $column, string $label = ''): Field\Number
    {
        return $this->addField(new Field\Number($column, $label));
    }

    public function textarea(string $column, string $label = ''): Field\Textarea
    {
        return $this->addField(new Field\Textarea($column, $label));
    }

    public function hidden(string $column, string $label = ''): Field\Hidden
    {
        return $this->addField(new Field\Hidden($column, $label));
    }

    public function display(string $column, string $label = ''): Field\Display
    {
        return $this->addField(new Field\Display($column, $label));
    }

    public function select(string $column, string $label = ''): Field\Select
    {
        return $this->addField(new Field\Select($column, $label));
    }

    public function multipleSelect(string $column, string $label = ''): Field\MultipleSelect
    {
        return $this->addField(new Field\MultipleSelect($column, $label));
    }

    public function radio(string $column, string $label = ''): Field\Radio
    {
        return $this->addField(new Field\Radio($column, $label));
    }

    public function checkbox(string $column, string $label = ''): Field\Checkbox
    {
        return $this->addField(new Field\Checkbox($column, $label));
    }

    public function switch(string $column, string $label = ''): Field\SwitchField
    {
        return $this->addField(new Field\SwitchField($column, $label));
    }

    public function date(string $column, string $label = ''): Field\Date
    {
        return $this->addField(new Field\Date($column, $label));
    }

    public function datetime(string $column, string $label = ''): Field\Datetime
    {
        return $this->addField(new Field\Datetime($column, $label));
    }

    public function file(string $column, string $label = ''): Field\File
    {
        return $this->addField(new Field\File($column, $label));
    }

    public function image(string $column, string $label = ''): Field\Image
    {
        return $this->addField(new Field\Image($column, $label));
    }

    public function belongsToMany(string $column, string $label = ''): Field\BelongsToMany
    {
        return $this->addField(new Field\BelongsToMany($column, $label));
    }

    public function edit(mixed $id): static
    {
        $this->isEditing = true;
        $this->model = $this->model->findOrFail($id);

        foreach ($this->fields as $field) {
            $field->setModel($this->model);
        }

        return $this;
    }

    public function validate(): void
    {
        $rules = [];
        foreach ($this->fields as $column => $field) {
            $rule = $field->getRules();
            if ($rule) {
                $rules[$column] = $rule;
            }
        }

        if (!empty($rules)) {
            Validator::make(request()->all(), $rules)->validate();
        }
    }

    protected function getResourceUrl(): string
    {
        if (request()->has('_previous_')) {
            return (string)request()->input('_previous_');
        }

        $url = $this->action ?: request()->getPathInfo();
        $segments = explode('/', trim($url, '/'));

        if ($this->isEditing && !empty($segments) && (is_numeric(end($segments)) || end($segments) === (string)$this->model->getKey())) {
            array_pop($segments);
        }

        return '/' . implode('/', $segments);
    }

    public function store(): mixed
    {
        $this->validate();

        $request = request();
        $this->inputs = array_merge($request->all(), $request->allFiles());

        foreach ($this->savingCallbacks as $callback) {
            $res = call_user_func($callback, $this);
            if ($res instanceof RedirectResponse) {
                return $res;
            }
        }

        $relations = [];
        $updates = [];

        foreach ($this->fields as $column => $field) {
            if (in_array($column, $this->ignored, true)) {
                continue;
            }

            $rawVal = $this->inputs[$column] ?? null;
            $val = $field->prepare($rawVal);

            if ($field instanceof Field\BelongsToMany) {
                $relations[$column] = (array)($this->inputs[$column] ?? $val);
            } elseif ($val !== null) {
                $updates[$column] = $val;
            }
        }

        foreach ($this->inputs as $key => $val) {
            if (in_array($key, $this->ignored, true) || isset($relations[$key]) || str_starts_with($key, '_')) {
                continue;
            }
            if (!isset($updates[$key]) && !isset($this->fields[$key])) {
                $updates[$key] = $val;
            }
        }

        $this->model->fill($updates);

        DB::transaction(function () use ($relations) {
            $this->model->save();

            foreach ($relations as $relationName => $ids) {
                if (method_exists($this->model, $relationName)) {
                    $relation = $this->model->{$relationName}();
                    if ($relation instanceof EloquentBelongsToMany) {
                        $relation->sync($ids);
                    }
                }
            }
        });

        foreach ($this->savedCallbacks as $callback) {
            $res = call_user_func($callback, $this);
            if ($res instanceof RedirectResponse) {
                return $res;
            }
        }

        veltrix_toastr(veltrix_trans('save_succeeded') ?: 'Saved successfully', 'success');

        return redirect($this->getResourceUrl() ?: veltrix_base_path());
    }

    public function update(mixed $id): mixed
    {
        $this->isEditing = true;
        $this->model = $this->model->findOrFail($id);

        foreach ($this->fields as $field) {
            $field->setModel($this->model);
        }

        $this->validate();

        $request = request();
        $this->inputs = array_merge($request->all(), $request->allFiles());

        foreach ($this->savingCallbacks as $callback) {
            $res = call_user_func($callback, $this);
            if ($res instanceof RedirectResponse) {
                return $res;
            }
        }

        $relations = [];
        $updates = [];

        foreach ($this->fields as $column => $field) {
            if (in_array($column, $this->ignored, true)) {
                continue;
            }

            $rawVal = $this->inputs[$column] ?? null;
            $val = $field->prepare($rawVal);

            if ($field instanceof Field\BelongsToMany) {
                $relations[$column] = (array)($this->inputs[$column] ?? $val);
            } elseif ($val !== null) {
                $updates[$column] = $val;
            }
        }

        foreach ($this->inputs as $key => $val) {
            if (in_array($key, $this->ignored, true) || isset($relations[$key]) || str_starts_with($key, '_')) {
                continue;
            }
            if (!isset($updates[$key]) && !isset($this->fields[$key])) {
                $updates[$key] = $val;
            }
        }

        $this->model->fill($updates);

        DB::transaction(function () use ($relations) {
            $this->model->save();

            foreach ($relations as $relationName => $ids) {
                if (method_exists($this->model, $relationName)) {
                    $relation = $this->model->{$relationName}();
                    if ($relation instanceof EloquentBelongsToMany) {
                        $relation->sync($ids);
                    }
                }
            }
        });

        foreach ($this->savedCallbacks as $callback) {
            $res = call_user_func($callback, $this);
            if ($res instanceof RedirectResponse) {
                return $res;
            }
        }

        veltrix_toastr(veltrix_trans('update_succeeded') ?: 'Updated successfully', 'success');

        return redirect($this->getResourceUrl() ?: veltrix_base_path());
    }

    public function destroy(mixed $id): mixed
    {
        $this->model = $this->model->findOrFail($id);

        foreach ($this->deletingCallbacks as $callback) {
            $res = call_user_func($callback, $this);
            if ($res !== null) {
                return $res;
            }
        }

        $this->model->delete();

        foreach ($this->deletedCallbacks as $callback) {
            call_user_func($callback, $this);
        }

        if (request()->expectsJson() || request()->is('*/_handle_*')) {
            return response()->json([
                'status'  => true,
                'message' => veltrix_trans('delete_succeeded') ?: 'Deleted successfully',
            ]);
        }

        veltrix_toastr(veltrix_trans('delete_succeeded') ?: 'Deleted successfully', 'success');
        return back();
    }

    public function render(): string
    {
        if ($this->isRendering) {
            return '';
        }

        $this->isRendering = true;

        try {
            $viewName = view()->exists('veltrix::form') ? 'veltrix::form' : 'admin::form';

            return view($viewName, [
                'fields'    => $this->fields,
                'action'    => $this->getAction(),
                'method'    => $this->isEditing ? 'PUT' : 'POST',
                'isEditing' => $this->isEditing,
                'model'     => $this->model,
                'errors'    => session('errors') ?: new \Illuminate\Support\ViewErrorBag(),
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

    public function input(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->inputs;
        }

        return data_get($this->inputs, $name, $default);
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->inputs)) {
            return $this->inputs[$name];
        }

        return $this->model->getAttribute($name);
    }

    public function __set(string $name, mixed $value)
    {
        $this->inputs[$name] = $value;
    }
}
