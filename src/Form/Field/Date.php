<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Date extends Field
{
    public function renderField(): string
    {
        $val = htmlspecialchars((string)$this->getValue(), ENT_QUOTES, 'UTF-8');
        return "<input type=\"date\" name=\"{$this->column}\" id=\"field-{$this->column}\" value=\"{$val}\" class=\"vx-input\">";
    }
}
