<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Services\PermissionService;
use ReflectionClass;
use ReflectionMethod;

class GeneratePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:generate {controller? : Specific controller to generate permissions for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate permissions based on controller methods';

    /**
     * The permission service.
     *
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PermissionService $permissionService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $specificController = $this->argument('controller');

        if ($specificController) {
            $controllerClass = $this->resolveControllerClass($specificController);
            if ($controllerClass) {
                $this->generatePermissionsForController($controllerClass);
            } else {
                $this->error("Controller not found: {$specificController}");
                return 1;
            }
        } else {
            $this->generateAllPermissions();
        }

        return 0;
    }

    /**
     * Generate permissions for all controllers.
     */
    protected function generateAllPermissions()
    {
        $this->info('Scanning controllers for permission generation...');

        $controllers = $this->getControllers();
        $bar = $this->output->createProgressBar(count($controllers));
        $bar->start();

        $generatedCount = 0;

        foreach ($controllers as $controller) {
            $permissions = $this->generatePermissionsForController($controller);
            $generatedCount += count($permissions);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Generated {$generatedCount} permissions from " . count($controllers) . " controllers.");
    }

    /**
     * Get all controller classes.
     *
     * @return array
     */
    protected function getControllers()
    {
        $controllers = [];
        $controllerPaths = [
            app_path('Http/Controllers'),
            app_path('Http/Controllers/Admin'),
            app_path('Http/Controllers/Auth'),
            // Add other controller directories as needed
        ];

        foreach ($controllerPaths as $path) {
            if (File::isDirectory($path)) {
                $files = File::allFiles($path);

                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $className = $this->getClassNameFromFile($file);
                        if ($className && class_exists($className)) {
                            $controllers[] = $className;
                        }
                    }
                }
            }
        }

        return $controllers;
    }

    /**
     * Get class name from file.
     *
     * @param \SplFileInfo $file
     * @return string|null
     */
    protected function getClassNameFromFile($file)
    {
        $content = File::get($file->getPathname());

        // Extract namespace
        preg_match('/namespace\s+([^;]+)/i', $content, $matches);
        $namespace = $matches[1] ?? null;

        // Extract class name
        preg_match('/class\s+(\w+)/i', $content, $matches);
        $className = $matches[1] ?? null;

        if ($namespace && $className) {
            return $namespace . '\\' . $className;
        }

        return null;
    }

    /**
     * Resolve controller class from input.
     *
     * @param string $controller
     * @return string|null
     */
    protected function resolveControllerClass($controller)
    {
        // Check if the input is a fully qualified class name
        if (class_exists($controller)) {
            return $controller;
        }

        // Try with App\Http\Controllers prefix
        $className = 'App\\Http\\Controllers\\' . $controller;
        if (class_exists($className)) {
            return $className;
        }

        // Try with App\Http\Controllers\Admin prefix
        $className = 'App\\Http\\Controllers\\Admin\\' . $controller;
        if (class_exists($className)) {
            return $className;
        }

        // If controller doesn't end with "Controller", append it
        if (!Str::endsWith($controller, 'Controller')) {
            return $this->resolveControllerClass($controller . 'Controller');
        }

        return null;
    }

    /**
     * Generate permissions for a specific controller.
     *
     * @param string $controllerClass
     * @return array
     */
    protected function generatePermissionsForController($controllerClass)
    {
        // Skip the base Controller class and abstract classes
        if ($controllerClass === 'App\\Http\\Controllers\\Controller' ||
            (new ReflectionClass($controllerClass))->isAbstract()) {
            return [];
        }

        $permissions = $this->permissionService->detectPermissions($controllerClass);

        if (!empty($permissions)) {
            $this->permissionService->syncPermissions($permissions);

            $controllerName = class_basename($controllerClass);
            $this->line("<info>{$controllerName}:</info> " . implode(', ', $permissions));
        }

        return $permissions;
    }
}