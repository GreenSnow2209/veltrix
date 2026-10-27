<?php

namespace Veltrix\Controllers;

use Illuminate\Routing\Controller;
use Veltrix\Layout\Content;

class AdminController extends Controller
{
    use HasResourceActions;

    protected string $title = 'Title';

    protected array $description = [];

    protected function title(): string
    {
        return $this->title;
    }

    public function index(Content $content): Content
    {
        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? veltrix_trans('list') ?: 'List')
            ->body($this->grid());
    }

    public function show(mixed $id, Content $content): Content
    {
        return $content
            ->title($this->title())
            ->description($this->description['show'] ?? veltrix_trans('show') ?: 'Detail')
            ->body($this->detail($id));
    }

    public function edit(mixed $id, Content $content): Content
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? veltrix_trans('edit') ?: 'Edit')
            ->body($this->form()->edit($id));
    }

    public function create(Content $content): Content
    {
        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? veltrix_trans('create') ?: 'Create')
            ->body($this->form());
    }

    protected function grid()
    {
        throw new \BadMethodCallException('Method grid() must be implemented in ' . static::class);
    }

    protected function form()
    {
        throw new \BadMethodCallException('Method form() must be implemented in ' . static::class);
    }

    protected function detail(mixed $id)
    {
        throw new \BadMethodCallException('Method detail() must be implemented in ' . static::class);
    }
}
