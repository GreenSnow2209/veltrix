<?php

namespace Veltrix\Auth\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationLog extends Model
{
    protected $fillable = ['user_id', 'path', 'method', 'ip', 'input'];

    public static array $methodColors = [
        'GET'    => 'green',
        'POST'   => 'yellow',
        'PUT'    => 'blue',
        'DELETE' => 'red',
        'PATCH'  => 'purple',
    ];

    public static array $methods = [
        'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH',
        'LINK', 'UNLINK', 'COPY', 'HEAD', 'PURGE',
    ];

    public function __construct(array $attributes = [])
    {
        $connection = config('veltrix.database.connection') ?: config('admin.database.connection') ?: config('database.default');
        $this->setConnection($connection);

        $table = config('veltrix.database.operation_log_table') ?: config('admin.database.operation_log_table', 'admin_operation_log');
        $this->setTable($table);

        parent::__construct($attributes);
    }

    public function user(): BelongsTo
    {
        $userModel = config('veltrix.database.users_model', config('admin.database.users_model', Administrator::class));
        return $this->belongsTo($userModel, 'user_id');
    }
}
