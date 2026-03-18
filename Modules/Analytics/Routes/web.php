<?php

use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\Backend\AnalyticsController;

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'admin']], function () {
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/chart-data', [AnalyticsController::class, 'chartData'])->name('chart_data');
        Route::get('/top-videos', [AnalyticsController::class, 'topVideos'])->name('top_videos');
    });
});
