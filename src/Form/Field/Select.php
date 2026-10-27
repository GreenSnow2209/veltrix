<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Select extends Field
{
    public function renderField(): string
    {
        $current = (string)$this->getValue();
        $optionsHtml = '<option value="">' . (veltrix_trans('choose') ?: 'Please select') . '</option>';

        foreach ($this->options as $key => $label) {
            $selected = ((string)$key === $current) ? 'selected' : '';
            $optionsHtml .= "<option value=\"{$key}\" {$selected}>{$label}</option>";
        }

        return "<select name=\"{$this->column}\" id=\"field-{$this->column}\" class=\"vx-select\">{$optionsHtml}</select>";
    }
}
