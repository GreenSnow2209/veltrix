<?php

namespace Veltrix\Widgets;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;

class Box implements Renderable, Htmlable, \Stringable
{
    protected string $title = '';
    protected mixed $content = '';
    protected mixed $footer = '';
    protected string $style = 'default';
    protected array $tools = [];

    public function __construct(string $title = '', mixed $content = '', mixed $footer = '')
    {
        $this->title = $title;
        $this->content = $content;
        $this->footer = $footer;
    }

    public function title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function content(mixed $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function footer(mixed $footer): static
    {
        $this->footer = $footer;
        return $this;
    }

    public function style(string $style): static
    {
        $this->style = $style;
        return $this;
    }

    public function tool(mixed $tool): static
    {
        $this->tools[] = $tool;
        return $this;
    }

    public function render(): string
    {
        $contentHtml = $this->content instanceof Renderable ? $this->content->render() : (string)$this->content;
        $footerHtml = $this->footer ? "<div class=\"vx-card-footer\">{$this->footer}</div>" : '';
        $toolsHtml = !empty($this->tools) ? '<div class="vx-card-tools">' . implode('', $this->tools) . '</div>' : '';

        $titleHtml = $this->title ? "<div class=\"vx-card-header\">
            <h3 class=\"vx-card-title\">{$this->title}</h3>
            {$toolsHtml}
        </div>" : '';

        return "<div class=\"vx-card vx-card-{$this->style}\">
            {$titleHtml}
            <div class=\"vx-card-body\">
                {$contentHtml}
            </div>
            {$footerHtml}
        </div>";
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
