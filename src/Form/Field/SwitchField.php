<?php

namespace Veltrix\Form\Field;

use Veltrix\Form\Field;

class SwitchField extends Field
{
    protected array $states = [
        'on'  => ['value' => 1, 'text' => 'ON'],
        'off' => ['value' => 0, 'text' => 'OFF'],
    ];

    public function states(array $states): static
    {
        $this->states = $states;
        return $this;
    }

    public function prepare(mixed $value): mixed
    {
        return $value ? ($this->states['on']['value'] ?? 1) : ($this->states['off']['value'] ?? 0);
    }

    public function renderField(): string
    {
        $onVal = $this->states['on']['value'] ?? 1;
        $checked = ($this->getValue() == $onVal) ? 'checked' : '';

        return "<label class=\"vx-switch\">
            <input type=\"hidden\" name=\"{$this->column}\" value=\"0\">
            <input type=\"checkbox\" name=\"{$this->column}\" id=\"field-{$this->column}\" value=\"{$onVal}\" {$checked} class=\"vx-switch-input\">
            <span class=\"vx-switch-slider\"></span>
        </label>";
    }
}
