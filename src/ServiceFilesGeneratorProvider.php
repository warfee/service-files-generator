<?php

namespace Warfee\ServiceFilesGenerator;

use Illuminate\Support\ServiceProvider;
use Warfee\ServiceFilesGenerator\Console\Commands\GenerateServiceFiles;

class ServiceFilesGeneratorProvider extends ServiceProvider
{
    public function boot()
    {

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/Helpers' => app_path('Helpers'),
            ], 'service-generator-helpers');

            $this->commands([
                GenerateServiceFiles::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->singleton('service-files-generator', function () {
            return new ServiceFilesGenerator();
        });
    }
}