<?php

namespace App\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // register & set the configuration only on production environment
        if (app()->isProduction()) {

            // make sure to use secure scheme in production environment
            URL::forceScheme('https');
            $this->app['request']->server->set('https', true);
        }

        // register & set the configuration only on local environment
        if ($this->app->isLocal() || config('app.env_ci_cd')) {

            // register the IDE Helper for local environment
            $this->app->register(IdeHelperServiceProvider::class);
        }

        // register the application macros
        $this->registerMacros();
    }

    /**
     * Register any application macros.
     *
     * @return void
     */
    private function registerMacros(): void
    {
        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage = 15, $total = null, $page = null, $pageName = 'page') {
                $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
                return new LengthAwarePaginator(
                    $this->forPage($page, $perPage),
                    $total ?: $this->count(),
                    $perPage,
                    $page,
                    [
                        'path' => LengthAwarePaginator::resolveCurrentPath(),
                        'pageName' => $pageName,
                    ]
                );
            });
        }
    }
}
