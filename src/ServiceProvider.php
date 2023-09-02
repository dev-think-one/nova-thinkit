<?php

namespace NovaThinKit;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/nova-thinkit.php' => config_path('nova-thinkit.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../lang' => resource_path('lang/vendor/nova-thinkit'),
            ], 'lang');

            $this->commands([
                //
            ]);
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'nova-thinkit');
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nova-thinkit.php', 'nova-thinkit');
    }
}
