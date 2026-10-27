<?php

namespace Veltrix\Console;

use Illuminate\Console\Command;

class PermissionCommand extends Command
{
    protected $signature = 'veltrix:permission';

    protected $aliases = ['admin:permission'];

    protected $description = 'List all configured Veltrix permissions';

    public function handle(): int
    {
        $permissionModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model'));
        $permissions = $permissionModel::all(['id', 'slug', 'name', 'http_method', 'http_path'])
            ->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'slug'        => $item->slug,
                    'name'        => $item->name,
                    'http_method' => is_array($item->http_method) ? implode(', ', $item->http_method) : (string)$item->http_method,
                    'http_path'   => $item->http_path,
                ];
            })
            ->toArray();

        $this->table(['ID', 'Slug', 'Name', 'HTTP Method', 'HTTP Path'], $permissions);

        return Command::SUCCESS;
    }
}
