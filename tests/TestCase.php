<?php

namespace NovaThinKit\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\NovaCoreServiceProvider;
use NovaThinKit\Tests\Fixtures\Models\Contact;
use NovaThinKit\Tests\Fixtures\NovaServiceProvider;
use Orchestra\Testbench\Database\MigrateProcessor;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        Storage::fake('baz');
        Storage::fake('feature-images');

        Artisan::call('nova:publish');
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Inertia\ServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            \NovaThinKit\ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();

        $migrator = new MigrateProcessor($this, [
            '--path'     => __DIR__ . '/Fixtures/migrations',
            '--realpath' => true,
        ]);
        $migrator->up();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('auth.providers', array_merge(
            $app['config']->get('auth.providers'),
            [
                'contacts' => [
                    'driver' => 'eloquent',
                    'model'  => Contact::class,
                ],
            ]
        ));

        $app['config']->set('auth.passwords', array_merge(
            $app['config']->get('auth.passwords'),
            [
                'contacts' => [
                    'provider' => 'contacts',
                    'table'    => 'password_reset_tokens',
                    'expire'   => 60,
                    'throttle' => 60,
                ],
            ]
        ));

        $app['config']->set('auth.guards', array_merge(
            $app['config']->get('auth.guards'),
            [
                'contact_web' => [
                    'driver'   => 'session',
                    'provider' => 'contacts',
                ],
            ]
        ));

        // $app['config']->set('nova-thinkit.some_key', 'some_value');
    }
}
