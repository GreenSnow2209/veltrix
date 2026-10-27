<?php

namespace Veltrix\Controllers;

use Veltrix\Grid\Grid;

class LogController extends AdminController
{
    protected function title(): string
    {
        return veltrix_trans('operation_log') ?: 'Operation Audit Log';
    }

    protected function grid(): Grid
    {
        $logModel = config('veltrix.database.operation_log_model', config('admin.database.operation_log_model', \Veltrix\Auth\Database\OperationLog::class));

        $grid = new Grid(new $logModel());

        $grid->model()->latest();

        $grid->column('id', 'ID')->sortable();
        $grid->column('user.name', veltrix_trans('user') ?: 'User');
        $grid->column('method', veltrix_trans('method') ?: 'Method')->display(function ($method) {
            $color = match (strtoupper((string)$method)) {
                'GET' => 'success',
                'POST' => 'primary',
                'PUT', 'PATCH' => 'warning',
                'DELETE' => 'danger',
                default => 'default',
            };
            return "<span class=\"vx-badge vx-badge-{$color}\">{$method}</span>";
        });

        $grid->column('path', veltrix_trans('path') ?: 'Path')->display(function ($path) {
            return "<code class=\"vx-code-inline\">{$path}</code>";
        });

        $grid->column('ip', 'IP');
        $grid->column('input', veltrix_trans('input') ?: 'Input')->display(function ($input) {
            $formatted = json_encode(json_decode((string)$input), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return "<pre class=\"vx-code-block\" style=\"max-height: 80px; overflow: auto; margin:0;\">{$formatted}</pre>";
        });

        $grid->column('created_at', veltrix_trans('created_at') ?: 'Created At')->sortable();

        $grid->disableCreateButton();

        $grid->filter(function ($filter) {
            $filter->equal('user_id', veltrix_trans('user') ?: 'User');
            $filter->equal('method', veltrix_trans('method') ?: 'Method');
            $filter->like('path', veltrix_trans('path') ?: 'Path');
            $filter->like('ip', 'IP');
        });

        return $grid;
    }
}
