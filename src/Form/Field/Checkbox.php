<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Checkbox extends Field
{
    public function prepare(mixed $value): mixed
    {
        return is_array($value) ? array_filter($value) : $value;
    }

    public function renderField(): string
    {
        $current = (array)$this->getValue();
        $output = '<div class="vx-checkbox-group">';

        foreach ($this->options as $key => $label) {
            $checked = in_array((string)$key, array_map('strval', $current), true) ? 'checked' : '';
            $output .= "<label class=\"vx-checkbox-item\">
                <input type=\"checkbox\" name=\"{$this->column}[]\" value=\"{$key}\" {$checked} class=\"vx-checkbox-input\">
                <span class=\"vx-checkbox-label\">{$label}</span>
            </label>";
        }

        $output .= '</div>';
        return $output;
    }
}
