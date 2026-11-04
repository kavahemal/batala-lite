<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $routeName = request()->route()?->getName();

            if (! $routeName) {
                return;
            }

            $moduleKey = explode('.', $routeName)[0];
            $permissions = config('permissions');

            if (isset($permissions[$moduleKey]['title'])) {
                $view->with('title', $permissions[$moduleKey]['title']);
            }
        });
    }
}
