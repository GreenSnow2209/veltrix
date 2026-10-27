<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Hidden extends Field
{
    public function renderField(): string
    {
        $val = htmlspecialchars((string)$this->getValue(), ENT_QUOTES, 'UTF-8');
        return "<input type=\"hidden\" name=\"{$this->column}\" value=\"{$val}\">";
    }

    public function render(): string
    {
        return $this->renderField();
    }
}
