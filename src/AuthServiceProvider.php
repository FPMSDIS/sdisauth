<?php

namespace Sdisauth;

use Sdisauth\Console\InstallAuthPackageCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            InstallAuthPackageCommand::class,
        ]);
    }

    public function boot()
    {   
        $this->registerRoutes();

        $this->publishWithOverwrite([
            __DIR__.'/resources/views' => resource_path('views'),
            __DIR__.'/database/seeders' => database_path('seeders'),
            __DIR__.'/config/sdisauth.php' => config_path('sdisauth.php'),
            // __DIR__.'/public' => public_path(),
            __DIR__.'/routes/web' => base_path('routes/web'),
            __DIR__.'/Models' => app_path('Models'),
        ]);
    }

    /**
     * Publie les fichiers en écrasant les existants.
     */
    protected function publishWithOverwrite(array $paths)
    {
        foreach ($paths as $from => $to) {
            $this->deleteExisting($to);
            $this->publishes([$from => $to], 'sdisauth', true);
        }
    }

    /**
     * Supprime les fichiers et dossiers existants avant la publication.
     */
    protected function deleteExisting($from, $to)
    {
        if (File::exists($to)) {
            if (File::isDirectory($to)) {
                if ($to === resource_path('views')) {
                    foreach (File::allFiles($to) as $file) {
                        if ($file->getFilename() !== 'welcome.blade.php') {
                            File::delete($file->getPathname());
                        }
                    }
                } else {
                    File::deleteDirectory($to);
                }
            }
        }
    }

    protected function registerRoutes()
    {
        if (!$this->app->routesAreCached()) {
            $routeFiles = File::allFiles(__DIR__.'/routes/web');

            foreach ($routeFiles as $routeFile) {
                require $routeFile->getPathname();
            }
        }
    }
}
