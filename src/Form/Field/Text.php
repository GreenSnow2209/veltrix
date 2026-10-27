<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Text extends Field
{
    public function renderField(): string
    {
        $val = htmlspecialchars((string)$this->getValue(), ENT_QUOTES, 'UTF-8');
        $placeholder = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';

        return "<input type=\"text\" name=\"{$this->column}\" id=\"field-{$this->column}\" value=\"{$val}\" class=\"vx-input\" {$placeholder}>";
    }
}
