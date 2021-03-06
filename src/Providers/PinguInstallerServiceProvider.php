<?php

namespace PinguInstaller\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use PinguInstaller\Http\Middleware\AlreadyInstalled;
use PinguInstaller\Http\Middleware\RedirectToInstall;
use Vkovic\LaravelCommando\Providers\CommandoServiceProvider;

class PinguInstallerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        if (pingu_installed()) {
            return;
        }
        
        $this->registerConfig();
        $this->registerViews();
        $this->registerAssets();
        /**
         * Creates a new middleware group 'install' and add a installation check on all web routes
         */
        $web = $router->getMiddlewareGroups()['web'];
        foreach ($web as $middleware) {
            $router->pushMiddlewareToGroup('install', $middleware);
        }
        if (!pingu_installed()) {
            $router->prependMiddlewareToGroup('web', RedirectToInstall::class);
        }

        \Asset::container('installer')->add('js', 'vendor/installer/installer.js');
        \Asset::container('installer')->add('css', 'vendor/installer/installer.css');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(CommandoServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'installer'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $sourcePath = __DIR__.'/../Resources/views';

        $this->loadViewsFrom($sourcePath, 'installer');
    }

    public function registerAssets()
    {
        $this->publishes([
            __DIR__.'/../Resources/assets/public' => public_path('vendor/installer')
        ],'installer-assets');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
