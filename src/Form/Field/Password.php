<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Password extends Field
{
    public function renderField(): string
    {
        $placeholder = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';

        return "<input type=\"password\" name=\"{$this->column}\" id=\"field-{$this->column}\" class=\"vx-input\" {$placeholder}>";
    }
}
