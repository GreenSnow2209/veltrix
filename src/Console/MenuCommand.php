<?php

namespace Veltrix\Console;

use Illuminate\Console\Command;

class MenuCommand extends Command
{
    protected $signature = 'veltrix:menu';

    protected $aliases = ['admin:menu'];

    protected $description = 'Display the Veltrix sidebar menu hierarchy';

    public function handle(): int
    {
        $menuModel = config('veltrix.database.menu_model', config('admin.database.menu_model'));
        $menus = $menuModel::all(['id', 'parent_id', 'order', 'title', 'icon', 'uri'])->toArray();

        $this->table(['ID', 'Parent', 'Order', 'Title', 'Icon', 'URI'], $menus);

        return Command::SUCCESS;
    }
}
