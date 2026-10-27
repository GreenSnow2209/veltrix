<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Textarea extends Field
{
    protected int $rows = 4;

    public function rows(int $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

    public function renderField(): string
    {
        $val = htmlspecialchars((string)$this->getValue(), ENT_QUOTES, 'UTF-8');
        $placeholder = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';

        return "<textarea name=\"{$this->column}\" id=\"field-{$this->column}\" rows=\"{$this->rows}\" class=\"vx-textarea\" {$placeholder}>{$val}</textarea>";
    }
}
