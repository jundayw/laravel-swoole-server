<?php

namespace Jundayw\LaravelSwooleServer;

use Illuminate\Support\ServiceProvider;

class SwooleServerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/swoole.php',
            'swoole'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/swoole.php' => config_path('swoole.php'),
            ], 'swoole-config');

            $this->commands([
                Commands\SwooleCommand::class,
            ]);
        }
    }
}