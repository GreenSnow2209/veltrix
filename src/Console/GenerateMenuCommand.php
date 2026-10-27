<?php

namespace Veltrix\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateMenuCommand extends Command
{
    protected $signature = 'veltrix:generate-menu
                            {title? : Menu item title}
                            {--uri= : Destination URI}
                            {--icon=circle : Menu icon}
                            {--parent=0 : Parent menu ID}';

    protected $aliases = ['admin:generate-menu'];

    protected $description = 'Generate a new menu item in the Veltrix sidebar';

    public function handle(): int
    {
        $menuModel = config('veltrix.database.menu_model', config('admin.database.menu_model'));

        $title = $this->argument('title') ?: $this->ask('Menu title');
        $uri = $this->option('uri') ?? $this->ask('URI', Str::kebab($title));
        $icon = $this->option('icon') ?: 'circle';
        $parentId = (int)$this->option('parent');

        $maxOrder = (int)$menuModel::max('order');

        $menu = $menuModel::create([
            'parent_id' => $parentId,
            'order'     => $maxOrder + 1,
            'title'     => $title,
            'icon'      => $icon,
            'uri'       => $uri,
        ]);

        $this->info("Menu item [{$title}] generated with ID {$menu->id}.");
        return Command::SUCCESS;
    }
}
