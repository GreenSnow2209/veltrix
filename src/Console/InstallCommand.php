<?php

namespace Veltrix\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Veltrix\Auth\Database\AdminTablesSeeder;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'veltrix:install
                            {--user= : Administrator username}
                            {--password= : Administrator password}
                            {--force : Force rewrite scaffolding files}';

    /**
     * Legacy command alias.
     */
    protected $aliases = ['admin:install'];

    /**
     * The console command description.
     */
    protected $description = 'Install Veltrix administrative panel and initialize database';

    protected string $directory = '';

    public function handle(): int
    {
        $this->info('Installing Veltrix Laravel Admin Framework...');
        $this->line('<comment>“Familiar to Build. Modern by Design.”</comment>');
        $this->newLine();

        $this->initDatabase();
        $this->initAdminDirectory();

        $prefix = config('veltrix.route.prefix', config('admin.route.prefix', 'admin'));
        $url = url($prefix);

        $this->newLine();
        $this->info("Veltrix installed successfully!");
        $this->line("Open your browser and navigate to: <info>{$url}</info>");

        return Command::SUCCESS;
    }

    protected function initDatabase(): void
    {
        $this->info('Running database migrations...');
        $this->call('migrate', ['--force' => true]);

        $userModel = config('veltrix.database.users_model', config('admin.database.users_model'));

        if ($userModel::count() === 0) {
            $username = $this->option('user') ?: 'admin';
            $password = $this->option('password');

            if (!$password) {
                if ($this->input->isInteractive()) {
                    $password = $this->secret('Enter administrator password (press enter to generate a secure random password)') ?: Str::random(16);
                } else {
                    $password = Str::random(16);
                }
            }

            AdminTablesSeeder::$defaultUsername = $username;
            AdminTablesSeeder::$defaultPassword = $password;

            $this->info('Seeding administrative tables...');
            $this->call('db:seed', [
                '--class' => AdminTablesSeeder::class,
                '--force' => true,
            ]);

            $this->newLine();
            $this->line('========================================================');
            $this->line('  INITIAL ADMINISTRATOR ACCOUNT CREATED SECURELY');
            $this->line('========================================================');
            $this->line("  Username : <info>{$username}</info>");
            $this->line("  Password : <info>{$password}</info>");
            $this->line('  (Store these credentials safely. You can change them later in Profile Settings.)');
            $this->line('========================================================');
        }
    }

    protected function initAdminDirectory(): void
    {
        $this->directory = veltrix_path();

        if (is_dir($this->directory) && !$this->option('force')) {
            $this->comment("Directory [{$this->directory}] already exists. Skipping scaffolding.");
            return;
        }

        $this->makeDir('/');
        $this->makeDir('Controllers');

        $this->createHomeController();
        $this->createAuthController();
        $this->createExampleController();
        $this->createBootstrapFile();
        $this->createRoutesFile();
    }

    protected function createHomeController(): void
    {
        $file = $this->directory . '/Controllers/HomeController.php';
        $contents = $this->getStub('HomeController');
        $this->laravel['files']->put(
            $file,
            str_replace('DummyNamespace', config('veltrix.route.namespace', 'App\\Admin\\Controllers'), $contents)
        );
        $this->line('<info>Created:</info> ' . $file);
    }

    protected function createAuthController(): void
    {
        $file = $this->directory . '/Controllers/AuthController.php';
        $contents = $this->getStub('AuthController');
        $this->laravel['files']->put(
            $file,
            str_replace('DummyNamespace', config('veltrix.route.namespace', 'App\\Admin\\Controllers'), $contents)
        );
        $this->line('<info>Created:</info> ' . $file);
    }

    protected function createExampleController(): void
    {
        $file = $this->directory . '/Controllers/ExampleController.php';
        $contents = $this->getStub('ExampleController');
        $this->laravel['files']->put(
            $file,
            str_replace('DummyNamespace', config('veltrix.route.namespace', 'App\\Admin\\Controllers'), $contents)
        );
        $this->line('<info>Created:</info> ' . $file);
    }

    protected function createBootstrapFile(): void
    {
        $file = $this->directory . '/bootstrap.php';
        $contents = $this->getStub('bootstrap');
        $this->laravel['files']->put($file, $contents);
        $this->line('<info>Created:</info> ' . $file);
    }

    protected function createRoutesFile(): void
    {
        $file = $this->directory . '/routes.php';
        $contents = $this->getStub('routes');
        $this->laravel['files']->put($file, $contents);
        $this->line('<info>Created:</info> ' . $file);
    }

    protected function getStub(string $name): string
    {
        return $this->laravel['files']->get(__DIR__ . "/stubs/{$name}.stub");
    }

    protected function makeDir(string $path = ''): void
    {
        $fullPath = rtrim($this->directory . '/' . trim($path, '/'), '/');
        $this->laravel['files']->makeDirectory($fullPath, 0755, true, true);
    }
}
