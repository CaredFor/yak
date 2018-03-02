<?php

namespace Benwilkins\Yak;

use Illuminate\Support\ServiceProvider;

class YakServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/yak.php' => config_path('yak.php'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/yak.php', 'yak');
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias(Yak::class, 'yak');
    }
}
