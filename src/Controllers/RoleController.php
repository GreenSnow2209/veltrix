<?php

namespace Veltrix\Controllers;

use Veltrix\Form\Form;
use Veltrix\Grid\Grid;
use Veltrix\Show\Show;

class RoleController extends AdminController
{
    protected function title(): string
    {
        return veltrix_trans('roles') ?: 'Roles';
    }

    protected function grid(): Grid
    {
        $roleModel = config('veltrix.database.roles_model', config('admin.database.roles_model'));

        $grid = new Grid(new $roleModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('slug', veltrix_trans('slug') ?: 'Slug')->sortable();
        $grid->column('name', veltrix_trans('name') ?: 'Name');
        $grid->column('permissions', veltrix_trans('permissions') ?: 'Permissions')->display(function ($permissions) {
            return collect($permissions)->pluck('name')->implode(', ');
        })->badge('primary');

        $grid->column('created_at', veltrix_trans('created_at') ?: 'Created At')->sortable();
        $grid->column('updated_at', veltrix_trans('updated_at') ?: 'Updated At')->sortable();

        $grid->quickSearch('name', 'slug');

        return $grid;
    }

    protected function detail(mixed $id): Show
    {
        $roleModel = config('veltrix.database.roles_model', config('admin.database.roles_model'));
        $show = new Show($roleModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('slug', veltrix_trans('slug') ?: 'Slug');
        $show->field('name', veltrix_trans('name') ?: 'Name');
        $show->field('permissions', veltrix_trans('permissions') ?: 'Permissions')->as(function ($permissions) {
            return collect($permissions)->pluck('name')->implode(', ');
        })->badge('primary');

        $show->field('created_at', veltrix_trans('created_at') ?: 'Created At');
        $show->field('updated_at', veltrix_trans('updated_at') ?: 'Updated At');

        return $show;
    }

    public function form(): Form
    {
        $roleModel = config('veltrix.database.roles_model', config('admin.database.roles_model'));
        $permissionModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model'));

        $form = new Form(new $roleModel());

        $form->display('id', 'ID');
        $form->text('slug', veltrix_trans('slug') ?: 'Slug')->required();
        $form->text('name', veltrix_trans('name') ?: 'Name')->required();

        $form->belongsToMany('permissions', veltrix_trans('permissions') ?: 'Permissions')
            ->options($permissionModel::all()->pluck('name', 'id'));

        $form->display('created_at', veltrix_trans('created_at') ?: 'Created At');
        $form->display('updated_at', veltrix_trans('updated_at') ?: 'Updated At');

        return $form;
    }
}
