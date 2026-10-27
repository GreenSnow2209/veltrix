<?php

namespace Veltrix\Controllers;

use Veltrix\Auth\Database\Permission;
use Veltrix\Form\Form;
use Veltrix\Grid\Grid;
use Veltrix\Show\Show;

class PermissionController extends AdminController
{
    protected function title(): string
    {
        return veltrix_trans('permissions') ?: 'Permissions';
    }

    protected function grid(): Grid
    {
        $permissionModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model'));

        $grid = new Grid(new $permissionModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('slug', veltrix_trans('slug') ?: 'Slug')->sortable();
        $grid->column('name', veltrix_trans('name') ?: 'Name');
        $grid->column('http_method', veltrix_trans('http_method') ?: 'HTTP Method')->display(function ($methods) {
            if (empty($methods)) {
                return '<span class="vx-badge vx-badge-default">ANY</span>';
            }
            return collect((array)$methods)->map(fn($m) => "<span class=\"vx-badge vx-badge-info\">{$m}</span>")->implode(' ');
        });
        $grid->column('http_path', veltrix_trans('http_path') ?: 'HTTP Path')->display(function ($path) {
            return "<pre class=\"vx-code-block\" style=\"margin:0;\">{$path}</pre>";
        });

        $grid->quickSearch('name', 'slug', 'http_path');

        return $grid;
    }

    protected function detail(mixed $id): Show
    {
        $permissionModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model'));
        $show = new Show($permissionModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('slug', veltrix_trans('slug') ?: 'Slug');
        $show->field('name', veltrix_trans('name') ?: 'Name');
        $show->field('http_method', veltrix_trans('http_method') ?: 'HTTP Method');
        $show->field('http_path', veltrix_trans('http_path') ?: 'HTTP Path');

        $show->field('created_at', veltrix_trans('created_at') ?: 'Created At');
        $show->field('updated_at', veltrix_trans('updated_at') ?: 'Updated At');

        return $show;
    }

    public function form(): Form
    {
        $permissionModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model'));

        $form = new Form(new $permissionModel());

        $form->display('id', 'ID');
        $form->text('slug', veltrix_trans('slug') ?: 'Slug')->required();
        $form->text('name', veltrix_trans('name') ?: 'Name')->required();

        $form->multipleSelect('http_method', veltrix_trans('http_method') ?: 'HTTP Method')
            ->options(array_combine(Permission::$httpMethods, Permission::$httpMethods));

        $form->textarea('http_path', veltrix_trans('http_path') ?: 'HTTP Path')
            ->help('Paths that match this rule, one per line. Asterisk wildcard is supported (e.g. /auth/users*).');

        $form->display('created_at', veltrix_trans('created_at') ?: 'Created At');
        $form->display('updated_at', veltrix_trans('updated_at') ?: 'Updated At');

        return $form;
    }
}
