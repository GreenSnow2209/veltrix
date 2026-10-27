<?php

namespace Veltrix\Layout;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Arr;
use Veltrix\Facades\Veltrix;

class Content implements Renderable, Htmlable, \Stringable
{
    protected string $title = ' ';
    protected string $description = ' ';
    protected array $breadcrumb = [];
    protected array $rows = [];
    protected ?array $view = null;

    public function __construct(?Closure $callback = null)
    {
        if ($callback instanceof Closure) {
            $callback($this);
        }
    }

    public function header(string $header = ''): static
    {
        return $this->title($header);
    }

    public function title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function description(string $description = ''): static
    {
        $this->description = $description;
        return $this;
    }

    public function breadcrumb(...$breadcrumb): static
    {
        $this->breadcrumb = (array)$breadcrumb;
        return $this;
    }

    public function body(mixed $content): static
    {
        return $this->row($content);
    }

    public function row(mixed $content): static
    {
        if ($content instanceof Closure) {
            $row = new Row();
            call_user_func($content, $row);
            $this->rows[] = $row;
        } elseif ($content instanceof Row) {
            $this->rows[] = $content;
        } else {
            $this->rows[] = new Row($content);
        }

        return $this;
    }

    public function view(string $view, array $data = []): static
    {
        $this->view = compact('view', 'data');
        return $this;
    }

    public function dump(...$vars): static
    {
        return $this->row(admin_dump(...$vars));
    }

    public function withSuccess(string $title = '', string $message = ''): static
    {
        veltrix_success($title, $message);
        return $this;
    }

    public function withError(string $title = '', string $message = ''): static
    {
        veltrix_error($title, $message);
        return $this;
    }

    public function withWarning(string $title = '', string $message = ''): static
    {
        veltrix_warning($title, $message);
        return $this;
    }

    public function withInfo(string $title = '', string $message = ''): static
    {
        veltrix_info($title, $message);
        return $this;
    }

    public function build(): string
    {
        $output = '';
        foreach ($this->rows as $row) {
            $output .= $row->render();
        }
        return $output;
    }

    protected function getUserData(): array
    {
        $user = Veltrix::user();
        if (!$user) {
            return [];
        }

        return Arr::only($user->toArray(), ['id', 'username', 'email', 'name', 'avatar']);
    }

    public function render(): string
    {
        $viewName = view()->exists('veltrix::content') ? 'veltrix::content' : 'admin::content';

        $items = [
            'header'      => $this->title,
            'title'       => $this->title,
            'description' => $this->description,
            'breadcrumb'  => $this->breadcrumb,
            '_content_'   => $this->build(),
            '_view_'      => $this->view,
            '_user_'      => $this->getUserData(),
        ];

        return view($viewName, $items)->render();
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
