<?php

namespace Veltrix\Controllers;

use Illuminate\Support\Facades\Hash;
use Veltrix\Form\Form;
use Veltrix\Grid\Grid;
use Veltrix\Show\Show;

class UserController extends AdminController
{
    protected function title(): string
    {
        return veltrix_trans('administrator_users') ?: 'Administrator Users';
    }

    protected function grid(): Grid
    {
        $userModel = config('veltrix.database.users_model', config('admin.database.users_model'));

        $grid = new Grid(new $userModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('username', veltrix_trans('username') ?: 'Username')->sortable();
        $grid->column('name', veltrix_trans('name') ?: 'Name');
        $grid->column('roles', veltrix_trans('roles') ?: 'Roles')->display(function ($roles) {
            return collect($roles)->pluck('name')->implode(', ');
        })->badge('primary');

        $grid->column('created_at', veltrix_trans('created_at') ?: 'Created At')->sortable();
        $grid->column('updated_at', veltrix_trans('updated_at') ?: 'Updated At')->sortable();

        $grid->quickSearch('username', 'name');

        $grid->filter(function ($filter) {
            $filter->like('username', veltrix_trans('username') ?: 'Username');
            $filter->like('name', veltrix_trans('name') ?: 'Name');
        });

        return $grid;
    }

    protected function detail(mixed $id): Show
    {
        $userModel = config('veltrix.database.users_model', config('admin.database.users_model'));
        $show = new Show($userModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('username', veltrix_trans('username') ?: 'Username');
        $show->field('name', veltrix_trans('name') ?: 'Name');
        $show->field('avatar', veltrix_trans('avatar') ?: 'Avatar')->image();
        $show->field('roles', veltrix_trans('roles') ?: 'Roles')->as(function ($roles) {
            return collect($roles)->pluck('name')->implode(', ');
        })->badge('primary');

        $show->field('created_at', veltrix_trans('created_at') ?: 'Created At');
        $show->field('updated_at', veltrix_trans('updated_at') ?: 'Updated At');

        return $show;
    }

    public function form(): Form
    {
        $userModel = config('veltrix.database.users_model', config('admin.database.users_model'));
        $roleModel = config('veltrix.database.roles_model', config('admin.database.roles_model'));
        $permissionModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model'));

        $form = new Form(new $userModel());

        $form->display('id', 'ID');
        $form->text('username', veltrix_trans('username') ?: 'Username')->required();
        $form->text('name', veltrix_trans('name') ?: 'Name')->required();
        $form->image('avatar', veltrix_trans('avatar') ?: 'Avatar');

        $form->password('password', veltrix_trans('password') ?: 'Password');
        $form->password('password_confirmation', veltrix_trans('password_confirmation') ?: 'Password Confirmation');
        $form->ignore(['password_confirmation']);

        $form->belongsToMany('roles', veltrix_trans('roles') ?: 'Roles')
            ->options($roleModel::all()->pluck('name', 'id'));

        $form->belongsToMany('permissions', veltrix_trans('permissions') ?: 'Permissions')
            ->options($permissionModel::all()->pluck('name', 'id'));

        $form->display('created_at', veltrix_trans('created_at') ?: 'Created At');
        $form->display('updated_at', veltrix_trans('updated_at') ?: 'Updated At');

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            } else {
                $form->ignore(['password']);
            }
        });

        return $form;
    }
}
