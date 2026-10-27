<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class MultipleSelect extends Field
{
    public function prepare(mixed $value): mixed
    {
        return is_array($value) ? array_filter($value) : $value;
    }

    public function renderField(): string
    {
        $current = (array)$this->getValue();
        $optionsHtml = '';

        foreach ($this->options as $key => $label) {
            $selected = in_array((string)$key, array_map('strval', $current), true) ? 'selected' : '';
            $optionsHtml .= "<option value=\"{$key}\" {$selected}>{$label}</option>";
        }

        return "<select name=\"{$this->column}[]\" id=\"field-{$this->column}\" multiple class=\"vx-select vx-select-multiple\">{$optionsHtml}</select>";
    }
}
