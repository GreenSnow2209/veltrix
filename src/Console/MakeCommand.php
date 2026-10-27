<?php

namespace Veltrix\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeCommand extends Command
{
    protected $signature = 'veltrix:make
                            {name : Name of the Admin Controller}
                            {--model= : Fully qualified model class}
                            {--title= : Custom page title}';

    protected $aliases = ['admin:make'];

    protected $description = 'Scaffold a new Veltrix Admin CRUD Controller';

    public function handle(): int
    {
        $name = $this->argument('name');
        $model = $this->option('model');
        $title = $this->option('title');

        if (!$model) {
            $model = $this->ask('Enter model class (e.g. App\\Models\\User)');
        }

        $modelClass = class_basename($model);
        $title = $title ?: Str::headline($modelClass);

        $controllerName = Str::finish($name, 'Controller');
        $namespace = config('veltrix.route.namespace', 'App\\Admin\\Controllers');

        $stub = $this->laravel['files']->get(__DIR__ . '/stubs/controller.stub');

        $content = str_replace(
            ['DummyNamespace', 'DummyClass', 'DummyModelNamespace', 'DummyModel', 'DummyTitle'],
            [$namespace, $controllerName, $model, $modelClass, $title],
            $stub
        );

        $dir = veltrix_path('Controllers');
        if (!is_dir($dir)) {
            $this->laravel['files']->makeDirectory($dir, 0755, true, true);
        }

        $targetFile = "{$dir}/{$controllerName}.php";

        if (file_exists($targetFile)) {
            $this->error("Controller [{$targetFile}] already exists!");
            return Command::FAILURE;
        }

        $this->laravel['files']->put($targetFile, $content);

        $this->info("Controller [{$targetFile}] created successfully!");
        $this->line("Add route in <comment>" . veltrix_path('routes.php') . "</comment>:");
        $resourceRoute = Str::kebab(Str::plural($modelClass));
        $this->line("  <info>\$router->resource('{$resourceRoute}', '{$controllerName}');</info>");

        return Command::SUCCESS;
    }
}
