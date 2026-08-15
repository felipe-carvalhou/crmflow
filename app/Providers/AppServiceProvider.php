<?php

namespace App\Providers;

use App\Models\Lead;
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
        // Contagem de leads por status, usada nos atalhos da sidebar em todo o admin.
        View::composer('layouts.sidebar', function ($view) {
            $view->with(
                'leadCountsPorStatus',
                Lead::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')
            );
        });
    }
}
