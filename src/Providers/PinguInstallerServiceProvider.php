<?php

namespace PinguInstaller\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use PinguInstaller\Http\Middleware\RedirectToInstall;

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
        $this->registerConfig();
        $this->registerViews();
        /**
         * Creates a new middleware group 'install' and add a installation check on all web routes
         */
        $web = $router->getMiddlewareGroups()['web'];
        foreach($web as $middleware){
            $router->pushMiddlewareToGroup('install', $middleware);
        }
        if(!pingu_installed()){
            $router->prependMiddlewareToGroup('web', RedirectToInstall::class);
        }
        
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('installer.php'),
        ], 'installer');
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/vendor/installer');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'installer');

        $this->loadViewsFrom($sourcePath, 'installer');
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
