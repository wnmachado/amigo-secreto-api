<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = "/";

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            $this->_getModules();
        });
    }

    private function _getModules()
    {
        $dir = new \DirectoryIterator(app_path('Modules'));

        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $modulePath = $fileinfo->getPathname();
                $this->_loadModuleRoutes($modulePath);
            }
        }
    }

    private function _loadModuleRoutes($modulePath)
    {
        // Verifica se existe a pasta Http/Routes
        $routesPath = $modulePath . '/Http/Routes';

        if (file_exists($routesPath) && is_dir($routesPath)) {
            $routes = new \DirectoryIterator($routesPath);

            foreach ($routes as $route) {
                if (!$route->isDir() && !$route->isDot() && $route->getExtension() === 'php') {
                    Route::prefix('api')
                        ->middleware(['api'])
                        ->namespace($this->namespace)
                        ->group($route->getPathname());
                }
            }
        }

        // Verifica subpastas recursivamente (para módulos aninhados como Integrations/PlugZapi)
        $subDirs = new \DirectoryIterator($modulePath);
        foreach ($subDirs as $subDir) {
            if ($subDir->isDir() && !$subDir->isDot()) {
                $this->_loadModuleRoutes($subDir->getPathname());
            }
        }
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
