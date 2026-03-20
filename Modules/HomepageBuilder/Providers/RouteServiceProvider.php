<?php

namespace Modules\HomepageBuilder\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\HomepageBuilder\Http\Controllers';

    public function boot(): void { parent::boot(); }

    public function map(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('HomepageBuilder', '/Routes/web.php'));
    }
}
