<?php

use Illuminate\Support\Facades\Route;
use Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController;

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'admin']], function () {
    // Routes fixes en PREMIER (avant les routes avec {id})
    Route::get('homepage-builder',                  [HomepageBuilderController::class, 'index'])->name('homepage-builder.index');
    Route::get('homepage-builder/create',           [HomepageBuilderController::class, 'create'])->name('homepage-builder.create');
    Route::get('homepage-builder/content-options',  [HomepageBuilderController::class, 'getContentOptionsAjax'])->name('homepage-builder.content-options');
    Route::post('homepage-builder/reorder',         [HomepageBuilderController::class, 'reorder'])->name('homepage-builder.reorder');
    Route::post('homepage-builder',                 [HomepageBuilderController::class, 'store'])->name('homepage-builder.store');

    // Routes avec {id} en DERNIER
    Route::get('homepage-builder/{id}/edit',        [HomepageBuilderController::class, 'edit'])->name('homepage-builder.edit');
    Route::put('homepage-builder/{id}',             [HomepageBuilderController::class, 'update'])->name('homepage-builder.update');
    Route::delete('homepage-builder/{id}',          [HomepageBuilderController::class, 'destroy'])->name('homepage-builder.destroy');
    Route::post('homepage-builder/{id}/toggle',     [HomepageBuilderController::class, 'toggleActive'])->name('homepage-builder.toggle');
});
