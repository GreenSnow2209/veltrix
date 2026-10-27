<?php

namespace Veltrix\Form\Field;

use Illuminate\Support\Collection;
use Veltrix\Form\Field;

class BelongsToMany extends Field
{
    public function getValue(): mixed
    {
        if ($this->value !== null) {
            return $this->value;
        }

        if ($this->model && method_exists($this->model, $this->column)) {
            $relation = $this->model->{$this->column}();
            $related = $relation->get();
            if ($related instanceof Collection) {
                return $related->pluck($related->first()?->getKeyName() ?: 'id')->all();
            }
        }

        return (array)$this->default;
    }

    public function prepare(mixed $value): mixed
    {
        return (array)$value;
    }

    public function renderField(): string
    {
        $current = (array)$this->getValue();
        $currentStrings = array_map('strval', $current);
        $output = '<div class="vx-checkbox-group">';

        foreach ($this->options as $key => $label) {
            $checked = in_array((string)$key, $currentStrings, true) ? 'checked' : '';
            $labelStr = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');
            $output .= "<label class=\"vx-checkbox-item\">
                <input type=\"checkbox\" name=\"{$this->column}[]\" value=\"{$key}\" {$checked} class=\"vx-checkbox-input\">
                <span class=\"vx-checkbox-label\">{$labelStr}</span>
            </label>";
        }

        $output .= '</div>';
        return $output;
    }
}
