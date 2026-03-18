<?php

namespace Modules\Analytics\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Analytics';
    protected $moduleNameLower = 'analytics';

    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            base_path('Modules/Analytics/Config/config.php'),
            $this->moduleNameLower
        );
    }

    public function registerViews()
    {
        $sourcePath = base_path('Modules/Analytics/Resources/views');
        $this->loadViewsFrom([$sourcePath], $this->moduleNameLower);
    }

    public function registerTranslations()
    {
        $this->loadTranslationsFrom(
            base_path('Modules/Analytics/Resources/lang'),
            $this->moduleNameLower
        );
    }
}
