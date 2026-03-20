<?php

namespace Modules\HomepageBuilder\Providers;

use Illuminate\Support\ServiceProvider;

class HomepageBuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'homepage-builder');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }

    public function register(): void {}
}
