<?php
use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\Backend\AnalyticsController;

use Modules\Analytics\Http\Controllers\Backend\FinanceController;
use Modules\Analytics\Http\Controllers\Backend\ExportController;

// Admin
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'admin']], function () {
    Route::get('analytics',                    [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/partner/{id}',       [AnalyticsController::class, 'partner'])->name('analytics.partner');
    Route::get('finance',                      [FinanceController::class, 'index'])->name('finance.index');
    Route::get('analytics/export',             [ExportController::class, 'analyticsExport'])->name('analytics.export');
    Route::get('finance/export',               [ExportController::class, 'financeExport'])->name('finance.export');
});

// Partenaire
Route::group(['prefix' => 'app', 'as' => 'partner.', 'middleware' => ['auth', 'role:partner']], function () {
    Route::get('partner-analytics',            [AnalyticsController::class, 'partnerSelf'])->name('analytics');
});
