<?php

namespace Veltrix\Layout;

use Closure;
use Illuminate\Contracts\Support\Renderable;

class Row implements Buildable, Renderable
{
    /**
     * @var Column[]
     */
    protected array $columns = [];

    protected array $class = [];

    public function __construct(mixed $content = '')
    {
        if (!empty($content)) {
            $this->column(12, $content);
        }
    }

    public function column(mixed $width, mixed $content): static
    {
        $column = new Column($content, $width);
        $this->columns[] = $column;
        return $this;
    }

    public function class(string|array $class): static
    {
        $this->class = (array)$class;
        return $this;
    }

    public function build(): void
    {
        echo $this->render();
    }

    public function render(): string
    {
        $classes = array_unique(array_merge(['vx-row', 'row'], $this->class));
        $classAttr = implode(' ', $classes);

        $output = "<div class=\"{$classAttr}\">";
        foreach ($this->columns as $column) {
            $output .= $column->render();
        }
        $output .= '</div>';

        return $output;
    }
}
