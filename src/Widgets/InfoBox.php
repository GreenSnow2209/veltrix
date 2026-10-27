<?php

namespace Veltrix\Widgets;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;

class InfoBox implements Renderable, Htmlable, \Stringable
{
    protected string $title;
    protected string $info;
    protected string $icon;
    protected string $color;
    protected ?string $link;

    public function __construct(string $title, string $icon, string $color, string $link, string $info)
    {
        $this->title = $title;
        $this->icon = $icon;
        $this->color = $color;
        $this->link = $link;
        $this->info = $info;
    }

    public function render(): string
    {
        $linkHtml = $this->link ? "<a href=\"{$this->link}\" class=\"vx-infobox-footer\">More info &rarr;</a>" : '';

        return "<div class=\"vx-infobox vx-infobox-{$this->color}\">
            <div class=\"vx-infobox-icon\">
                <span class=\"vx-icon-{$this->icon}\"></span>
            </div>
            <div class=\"vx-infobox-content\">
                <span class=\"vx-infobox-text\">{$this->title}</span>
                <span class=\"vx-infobox-number\">{$this->info}</span>
            </div>
            {$linkHtml}
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
