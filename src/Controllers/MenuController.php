<?php

namespace Veltrix\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Veltrix\Form\Form;
use Veltrix\Layout\Column;
use Veltrix\Layout\Content;
use Veltrix\Layout\Row;
use Veltrix\Tree\Tree;

class MenuController extends Controller
{
    use HasResourceActions;

    public function index(Content $content): Content
    {
        return $content
            ->title(veltrix_trans('menu') ?: 'Menu Hierarchy')
            ->description(veltrix_trans('list') ?: 'Management')
            ->row(function (Row $row) {
                $row->column(6, $this->treeView());

                $row->column(6, function (Column $column) {
                    $form = $this->form();
                    $form->setAction(veltrix_base_path('auth/menu'));
                    $column->append($form);
                });
            });
    }

    public function show(mixed $id): RedirectResponse
    {
        return redirect(veltrix_base_path("auth/menu/{$id}/edit"));
    }

    public function edit(mixed $id, Content $content): Content
    {
        return $content
            ->title(veltrix_trans('menu') ?: 'Menu')
            ->description(veltrix_trans('edit') ?: 'Edit Menu Item')
            ->body($this->form()->edit($id));
    }

    protected function treeView(): Tree
    {
        $menuModel = config('veltrix.database.menu_model', config('admin.database.menu_model'));
        $tree = new Tree(new $menuModel());
        $tree->disableCreate();

        $tree->branch(function ($branch) {
            $title = $branch['title'] ?? '';
            $icon = $branch['icon'] ?? 'circle';
            $uri = $branch['uri'] ?? '';
            $id = $branch['id'];

            $url = $uri ? (url()->isValidUrl($uri) ? $uri : veltrix_url($uri)) : '';
            $urlLink = $url ? "<a href=\"{$url}\" class=\"vx-link\" style=\"margin-left: 8px; font-size: 13px;\">/{$uri}</a>" : '';

            $editUrl = veltrix_base_path("auth/menu/{$id}/edit");
            $actions = "<div class=\"vx-tree-actions\" style=\"float: right;\">
                <a href=\"{$editUrl}\" class=\"vx-btn vx-btn-xs vx-btn-subtle\">Edit</a>
            </div>";

            return "<span class=\"vx-tree-node-title\"><strong>{$title}</strong> {$urlLink}</span> {$actions}";
        });

        return $tree;
    }

    public function form(): Form
    {
        $menuModel = config('veltrix.database.menu_model', config('admin.database.menu_model'));
        $roleModel = config('veltrix.database.roles_model', config('admin.database.roles_model'));
        $permissionModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model'));

        $form = new Form(new $menuModel());

        $form->select('parent_id', veltrix_trans('parent_id') ?: 'Parent')->options($menuModel::selectOptions());
        $form->text('title', veltrix_trans('title') ?: 'Title')->required();
        $form->text('icon', veltrix_trans('icon') ?: 'Icon')->default('circle')->required();
        $form->text('uri', veltrix_trans('uri') ?: 'URI');

        $form->belongsToMany('roles', veltrix_trans('roles') ?: 'Roles')
            ->options($roleModel::all()->pluck('name', 'id'));

        $form->select('permission', veltrix_trans('permission') ?: 'Permission')
            ->options($permissionModel::all()->pluck('name', 'slug'));

        return $form;
    }
}
