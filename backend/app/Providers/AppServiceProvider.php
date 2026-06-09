<?php

namespace App\Providers;

use App\Services\Apifon\ApifonClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApifonClient::class, fn () => ApifonClient::fromConfig());
    }

    public function boot(): void
    {
        //
    }
}
