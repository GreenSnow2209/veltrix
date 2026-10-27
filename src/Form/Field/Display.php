<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class Display extends Field
{
    public function renderField(): string
    {
        $val = htmlspecialchars((string)$this->getValue(), ENT_QUOTES, 'UTF-8');
        return "<div class=\"vx-form-static\">{$val}</div>";
    }

    public function prepare(mixed $value): mixed
    {
        return null; // Display fields are read-only and not persisted
    }
}
