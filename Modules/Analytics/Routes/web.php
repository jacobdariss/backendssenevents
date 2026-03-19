<?php
use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\Backend\AnalyticsController;

// Admin
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'admin']], function () {
    Route::get('analytics',                    [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/partner/{id}',       [AnalyticsController::class, 'partner'])->name('analytics.partner');
});

// Partenaire
Route::group(['prefix' => 'app', 'as' => 'partner.', 'middleware' => ['auth', 'role:partner']], function () {
    Route::get('partner-analytics',            [AnalyticsController::class, 'partnerSelf'])->name('analytics');
});
