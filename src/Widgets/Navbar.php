<?php

namespace Veltrix\Widgets;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;

class Navbar implements Renderable
{
    /**
     * @var array<string, Collection>
     */
    protected array $elements = [];

    public function __construct()
    {
        $this->elements = [
            'left'  => collect(),
            'right' => collect(),
        ];
    }

    public function left(mixed $element): static
    {
        $this->elements['left']->push($element);
        return $this;
    }

    public function right(mixed $element): static
    {
        $this->elements['right']->push($element);
        return $this;
    }

    public function add(mixed $element): static
    {
        return $this->right($element);
    }

    public function render(mixed $part = 'right'): string
    {
        $part = (string)$part;

        if (!isset($this->elements[$part]) || $this->elements[$part]->isEmpty()) {
            return '';
        }

        return $this->elements[$part]->map(function ($element) {
            if ($element instanceof Htmlable) {
                return $element->toHtml();
            }

            if ($element instanceof Renderable) {
                return $element->render();
            }

            return (string)$element;
        })->implode('');
    }
}
