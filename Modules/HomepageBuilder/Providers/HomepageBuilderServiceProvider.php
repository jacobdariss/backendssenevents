<?php

namespace Modules\HomepageBuilder\Providers;

use Illuminate\Support\ServiceProvider;

class HomepageBuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'homepage-builder');
        // Routes enregistrées dans routes/web.php global
    }

    public function register(): void {}
}
