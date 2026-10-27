<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Radio extends Field
{
    public function renderField(): string
    {
        $current = (string)$this->getValue();
        $output = '<div class="vx-radio-group">';

        foreach ($this->options as $key => $label) {
            $checked = ((string)$key === $current) ? 'checked' : '';
            $output .= "<label class=\"vx-radio-item\">
                <input type=\"radio\" name=\"{$this->column}\" value=\"{$key}\" {$checked} class=\"vx-radio-input\">
                <span class=\"vx-radio-label\">{$label}</span>
            </label>";
        }

        $output .= '</div>';
        return $output;
    }
}
