<?php

namespace Veltrix\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'veltrix:publish
                            {--force : Overwrite any existing files}
                            {--config : Publish configuration only}
                            {--assets : Publish frontend assets only}
                            {--lang : Publish language files only}
                            {--views : Publish Blade views only}
                            {--migrations : Publish database migrations only}';

    protected $aliases = ['admin:publish'];

    protected $description = "Publish Veltrix configuration, assets, translations, or views";

    public function handle(): int
    {
        $force = $this->option('force');

        if ($this->option('config')) {
            $this->call('vendor:publish', ['--tag' => 'veltrix-config', '--force' => $force]);
            return Command::SUCCESS;
        }

        if ($this->option('assets')) {
            $this->call('vendor:publish', ['--tag' => 'veltrix-assets', '--force' => $force]);
            return Command::SUCCESS;
        }

        if ($this->option('lang')) {
            $this->call('vendor:publish', ['--tag' => 'veltrix-lang', '--force' => $force]);
            return Command::SUCCESS;
        }

        if ($this->option('views')) {
            $this->call('vendor:publish', ['--tag' => 'veltrix-views', '--force' => $force]);
            return Command::SUCCESS;
        }

        if ($this->option('migrations')) {
            $this->call('vendor:publish', ['--tag' => 'veltrix-migrations', '--force' => $force]);
            return Command::SUCCESS;
        }

        $this->call('vendor:publish', ['--tag' => 'veltrix-config', '--force' => $force]);
        $this->call('vendor:publish', ['--tag' => 'veltrix-assets', '--force' => $force]);
        $this->call('vendor:publish', ['--tag' => 'veltrix-lang', '--force' => $force]);

        $this->info('Veltrix resources published successfully.');
        return Command::SUCCESS;
    }
}
