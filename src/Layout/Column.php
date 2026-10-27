<?php

namespace Veltrix\Layout;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;

class Column implements Buildable, Renderable
{
    protected array $width = [];
    protected array $contents = [];

    public function __construct(mixed $content, mixed $width = 12)
    {
        if ($content instanceof Closure) {
            call_user_func($content, $this);
        } else {
            $this->append($content);
        }

        if (is_numeric($width)) {
            $this->width = ['md' => (int)$width];
        } elseif (is_array($width)) {
            $this->width = $width;
        } else {
            $this->width = ['md' => 12];
        }
    }

    public function append(mixed $content): static
    {
        $this->contents[] = $content;
        return $this;
    }

    public function row(mixed $content): static
    {
        $row = new Row();
        if ($content instanceof Closure) {
            call_user_func($content, $row);
        } else {
            $row->column(12, $content);
        }

        return $this->append($row->render());
    }

    public function build(): void
    {
        echo $this->render();
    }

    public function render(): string
    {
        $classes = ['vx-col'];
        foreach ($this->width as $key => $val) {
            $classes[] = "vx-col-{$val}";
            $classes[] = "col-{$key}-{$val}";
        }

        $classAttr = implode(' ', array_unique($classes));
        $output = "<div class=\"{$classAttr}\">";

        foreach ($this->contents as $content) {
            if ($content instanceof Htmlable) {
                $output .= $content->toHtml();
            } elseif ($content instanceof Renderable) {
                $output .= $content->render();
            } else {
                $output .= (string)$content;
            }
        }

        $output .= '</div>';
        return $output;
    }
}
