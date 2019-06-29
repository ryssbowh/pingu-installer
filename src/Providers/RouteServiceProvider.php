<?php

namespace PinguInstaller\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The root namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $namespace = 'PinguInstaller\Http\Controllers';

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    public function map()
    {
        Route::middleware('install')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/web.php');
    }
}
