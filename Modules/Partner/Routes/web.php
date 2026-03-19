<?php

use Illuminate\Support\Facades\Route;
use Modules\Partner\Http\Controllers\Backend\PartnerController;
use Modules\Partner\Http\Controllers\Backend\PartnerValidationController;
use Modules\Partner\Http\Controllers\Frontend\PartnerAuthController;
use Modules\Partner\Http\Controllers\Frontend\PartnerDashboardController;
use Modules\Partner\Http\Controllers\Frontend\PartnerVideoController;
use Modules\Partner\Http\Controllers\Frontend\PartnerContentController;
use Modules\Partner\Http\Controllers\Frontend\PartnerLiveTvController;

/*
|--------------------------------------------------------------------------
| Partner Frontend Routes (public)
|--------------------------------------------------------------------------
*/
Route::prefix('partner')->name('partner.')->group(function () {
    Route::get('register', [PartnerAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [PartnerAuthController::class, 'register'])->name('register.store');

    Route::get('login',  [PartnerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [PartnerAuthController::class, 'login'])->name('login.store');
    Route::post('logout',[PartnerAuthController::class, 'logout'])->name('logout');

    Route::get('2fa',           [PartnerAuthController::class, 'show2FA'])->name('2fa');
    Route::post('2fa/verify',   [PartnerAuthController::class, 'verify2FA'])->name('2fa.verify');
    Route::post('2fa/resend',   [PartnerAuthController::class, 'resend2FA'])->name('2fa.resend');
});

/*
|--------------------------------------------------------------------------
| Partner Dashboard Routes (espace partenaire dans /app/)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'app', 'as' => 'partner.', 'middleware' => ['auth', 'role:partner']], function () {
    Route::get('partner-dashboard', [PartnerDashboardController::class, 'index'])->name('dashboard');
    Route::get('partner-videos',             [PartnerDashboardController::class, 'videos'])->name('videos');
    Route::get('partner-videos/create',      [PartnerVideoController::class, 'create'])->name('videos.create');
    Route::post('partner-videos/store',      [PartnerVideoController::class, 'store'])->name('videos.store');
    Route::get('partner-videos/{id}/edit',   [PartnerVideoController::class, 'edit'])->name('videos.edit');
    Route::put('partner-videos/{id}/update', [PartnerVideoController::class, 'update'])->name('videos.update');
    Route::get('partner-movies',              [PartnerDashboardController::class, 'movies'])->name('movies');
    Route::get('partner-movies/create',       [PartnerContentController::class, 'moviesCreate'])->name('movies.create');
    Route::post('partner-movies/store',       [PartnerContentController::class, 'moviesStore'])->name('movies.store');
    Route::get('partner-movies/{id}/edit',    [PartnerContentController::class, 'moviesEdit'])->name('movies.edit');
    Route::put('partner-movies/{id}/update',  [PartnerContentController::class, 'moviesUpdate'])->name('movies.update');

    Route::get('partner-tvshows',             [PartnerDashboardController::class, 'tvshows'])->name('tvshows');
    Route::get('partner-tvshows/create',      [PartnerContentController::class, 'tvshowsCreate'])->name('tvshows.create');
    Route::post('partner-tvshows/store',      [PartnerContentController::class, 'tvshowsStore'])->name('tvshows.store');
    Route::get('partner-tvshows/{id}/edit',   [PartnerContentController::class, 'tvshowsEdit'])->name('tvshows.edit');
    Route::put('partner-tvshows/{id}/update', [PartnerContentController::class, 'tvshowsUpdate'])->name('tvshows.update');

    Route::get('partner-livetv',              [PartnerDashboardController::class, 'livetv'])->name('livetv');
    Route::get('partner-livetv/create',       [PartnerLiveTvController::class, 'create'])->name('livetv.create');
    Route::post('partner-livetv/store',       [PartnerLiveTvController::class, 'store'])->name('livetv.store');
    Route::get('partner-livetv/{id}/edit',    [PartnerLiveTvController::class, 'edit'])->name('livetv.edit');
    Route::put('partner-livetv/{id}/update',  [PartnerLiveTvController::class, 'update'])->name('livetv.update');
});

/*
|--------------------------------------------------------------------------
| Partner Backend Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'admin']], function () {

    Route::group(['prefix' => '/partners', 'as' => 'partners.'], function () {
        Route::get('/index_data', [PartnerController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [PartnerController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [PartnerController::class, 'update_status'])->name('update_status');
        Route::post('restore/{id}', [PartnerController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{id}', [PartnerController::class, 'forceDelete'])->name('force_delete');
    });

    Route::resource('partners', PartnerController::class)->names('partners');

    // Partner content validation
    Route::prefix('partner-validation')->name('partner-validation.')->group(function () {
        Route::get('/', [PartnerValidationController::class, 'index'])->name('index');
        Route::post('approve/{contentType}/{id}', [PartnerValidationController::class, 'approve'])->name('approve');
        Route::post('reject/{contentType}/{id}', [PartnerValidationController::class, 'reject'])->name('reject');
    });
});
