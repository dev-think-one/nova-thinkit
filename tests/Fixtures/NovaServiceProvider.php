<?php

namespace NovaThinKit\Tests\Fixtures;

use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use NovaThinKit\Tests\Fixtures\Nova\Resources\Contact;
use NovaThinKit\Tests\Fixtures\Nova\Resources\Page;
use NovaThinKit\Tests\Fixtures\Nova\Resources\Post;
use NovaThinKit\Tests\Fixtures\Nova\ResourceTemplates\Pages\HomePageTemplate;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        \NovaThinKit\Nova\ResourceTemplates\TemplateFinder::templatesMap(\NovaThinKit\Tests\Fixtures\Models\Page::class, [
            'home'           => HomePageTemplate::class,
        ]);
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return true;
        });
    }


    protected function dashboards()
    {
        return [
        ];
    }

    protected function resources()
    {
        Nova::resources([
            Post::class,
            Page::class,
            Contact::class,
        ]);
    }
}
