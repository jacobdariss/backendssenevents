<?php

use App\Http\Controllers\Backend\AdminManagementController;
use App\Http\Controllers\Backend\SecurityController;
use App\Http\Controllers\Backend\BackendController;
use App\Http\Controllers\Backend\BackupController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermission;
use App\Http\Controllers\SearchController;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\MobileSettingController;
use Modules\Setting\Http\Controllers\Backend\SettingsController;
use Modules\Frontend\Http\Controllers\FrontendController;
use App\Http\Controllers\Auth\WebQrLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth Routes
require __DIR__ . '/auth.php';



// ── PHPUnit Runner — TEMPORAIRE, supprimer après usage ───────────────────────
Route::get('/dev-run-tests', function () {
    if (request('token') !== 'sen2026tests') {
        abort(403, 'Token invalide');
    }

    $php = '/opt/plesk/php/8.4/bin/php';

    // Auto-nettoyage du cache routes/views/config au premier appel
    exec($php . ' ' . escapeshellarg(base_path('artisan')) . ' route:clear 2>&1');
    exec($php . ' ' . escapeshellarg(base_path('artisan')) . ' view:clear 2>&1');
    exec($php . ' ' . escapeshellarg(base_path('artisan')) . ' config:clear 2>&1');

    $suite  = request('suite', 'Feature');
    $filter = request('filter', '');
    $php     = '/opt/plesk/php/8.4/bin/php';
    $phpunit = base_path('vendor/bin/phpunit');
    $config  = base_path('phpunit.xml');

    $cmd = escapeshellcmd($php) . ' '
         . escapeshellarg($phpunit)
         . ' --testsuite=' . escapeshellarg($suite)
         . ' --colors=never'
         . ' --configuration=' . escapeshellarg($config);

    if ($filter) {
        $cmd .= ' --filter=' . escapeshellarg($filter);
    }

    $start = microtime(true);
    exec($cmd . ' 2>&1', $output, $exitCode);
    $elapsed = round(microtime(true) - $start, 2);
    $raw = implode("\n", $output);

    preg_match('/(\d+) passed/', $raw, $p);
    preg_match('/(\d+) failed/', $raw, $f);
    preg_match('/(\d+) error/', $raw, $e);
    preg_match('/Tests: (\d+)/', $raw, $t);

    $passed = (int)($p[1] ?? 0);
    $failed = (int)($f[1] ?? 0);
    $errors = (int)($e[1] ?? 0);
    $total  = (int)($t[1] ?? $passed + $failed + $errors);
    $ok     = $exitCode === 0;

    $suites = ['Feature', 'Unit'];
    $token  = 'sen2026tests';

    return response(view('phpunit_runner', compact(
        'raw', 'passed', 'failed', 'errors', 'total',
        'elapsed', 'exitCode', 'ok', 'suite', 'filter',
        'suites', 'token'
    )));
})->withoutMiddleware([\App\Http\Middleware\CheckInstallation::class]);

Route::group(['middleware' => ['checkInstallation']], function () {

Route::get('/', [FrontendController::class, 'index'])->name('user.login');
Route::get('/web-qr-status/{id}', [WebQrLoginController::class, 'checkStatus'])->name('web-qr-status');


Route::group(['prefix' => 'app', 'middleware' => ['auth','admin']], function () {
    // Language Switch
    Route::get('language/{language}', [LanguageController::class, 'switch'])->name('language.switch');
    Route::post('set-user-setting', [BackendController::class, 'setUserSetting'])->name('backend.setUserSetting');
    Route::post('check-in-trash', [SearchController::class, 'check_in_trash'])->name('check-in-trash');
    Route::group(['as' => 'backend.', 'middleware' => ['auth','admin','admin.timeout']], function () {
        Route::post('update-player-id', [UserController::class, 'update_player_id'])->name('update-player-id');
        Route::get('get_search_data', [SearchController::class, 'get_search_data'])->name('get_search_data');

        // Admin Management — accessible via /app/permission-role
        Route::get('/permission-role', [AdminManagementController::class, 'index'])->name('permission-role.list')->middleware('password.confirm');
        Route::post('/permission-role/store/{role_id}', [RolePermission::class, 'store'])->name('permission-role.store');
        Route::get('/permission-role/reset/{role_id}', [RolePermission::class, 'reset_permission'])->name('permission-role.reset');
        // Role & Permissions Crud
        Route::resource('permission', PermissionController::class);
        Route::resource('role', RoleController::class);

        Route::group(['prefix' => 'module', 'as' => 'module.'], function () {
            Route::get('index_data', [ModuleController::class, 'index_data'])->name('index_data');
            Route::post('update-status/{id}', [ModuleController::class, 'update_status'])->name('update_status');
        });

        Route::resource('module', ModuleController::class);

        /*
          *
          *  Settings Routes
          *
          * ---------------------------------------------------------------------
          */
        Route::group(['middleware' => ['admin']], function () {
            Route::get('settings/{vue_capture?}', [SettingController::class, 'index'])->name('settings')->where('vue_capture', '^(?!storage).*$');
            Route::get('settings-data', [SettingController::class, 'index_data']);
            Route::post('settings', [SettingController::class, 'store'])->name('settings.store');
           // Route::post('setting-update', [SettingController::class, 'update'])->name('setting.update');
            Route::get('clear-cache', [SettingController::class, 'clear_cache'])->name('clear-cache');
            Route::post('verify-email', [SettingController::class, 'verify_email'])->name('verify-email');
        });

        /*
        *
        *  Notification Routes
        *
        * ---------------------------------------------------------------------
        */

        /*
        *
        *  Backup Routes
        *
        * ---------------------------------------------------------------------
        */
        Route::group(['prefix' => 'backups', 'as' => 'backups.'], function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::get('/create', [BackupController::class, 'create'])->name('create');
            Route::get('/download/{file_name}', [BackupController::class, 'download'])->name('download');
            Route::get('/delete/{file_name}', [BackupController::class, 'delete'])->name('delete');
        });

    });

    /*
    *
    * Backend Routes
    * These routes need view-backend permission
    * --------------------------------------------------------------------
    */
    Route::group(['as' => 'backend.', 'middleware' => ['auth','admin','admin.timeout']], function () {

        Route::get('/dashboard', [BackendController::class, 'index'])->name('home');
        Route::get('/daterange', [BackendController::class, 'daterange'])->name('daterange');

        // Admin Management (Users & Roles)
        Route::group(['prefix' => 'admin-management', 'as' => 'admin-management.'], function () {
            Route::get('/', [AdminManagementController::class, 'index'])->name('index');
            Route::post('users', [AdminManagementController::class, 'storeAdmin'])->name('users.store');
            Route::patch('users/{id}/role', [AdminManagementController::class, 'updateAdminRole'])->name('users.update-role');
            Route::delete('users/{id}', [AdminManagementController::class, 'destroyAdmin'])->name('users.destroy');
            Route::post('roles', [AdminManagementController::class, 'storeRole'])->name('roles.store');
            Route::delete('roles/{id}', [AdminManagementController::class, 'destroyRole'])->name('roles.destroy');
        });
        // Security (2FA + Permissions) — Super Admin only
        Route::group(['prefix' => 'setting/security', 'as' => 'security.'], function () {
            Route::get('/', [SecurityController::class, 'index'])->name('index');
            Route::post('2fa/toggle', [SecurityController::class, 'toggle2FA'])->name('2fa.toggle');
        });

        Route::get('google-auth', [BackendController::class, 'googleAuth'])->name('google-auth');
        Route::get('/get_revnue_chart_data/{type}', [BackendController::class, 'getRevenuechartData']);
        Route::get('/get_subscriber_chart_data/{type}', [BackendController::class, 'getSubscriberChartData']);
        Route::get('/get_genre_chart_data', [BackendController::class, 'getGenreChartData']);
        Route::get('/get_mostwatch_chart_data/{type}', [BackendController::class, 'getMostwatchChartData']);
        Route::get('/get_toprated_chart_data', [BackendController::class, 'getTopRatedChartData']);

        Route::group(['prefix' => ''], function () {

            /*
            *
            *  Users Routes
            *
            * ---------------------------------------------------------------------
            */
            Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
                Route::get('user-list', [UserController::class, 'user_list'])->name('user_list');
                Route::get('emailConfirmationResend/{id}', [UserController::class, 'emailConfirmationResend'])->name('emailConfirmationResend');
                Route::post('create-customer', [UserController::class, 'create_customer'])->name('create_customer');
                Route::post('information', [UserController::class, 'update'])->name('information');
                Route::post('change-password', [UserController::class, 'change_password'])->name('change_password');
                Route::post('import', [UserController::class, 'import'])->name('import');
                Route::get('download-sample', [UserController::class, 'downloadSample'])->name('download_sample');

            });
        });
        Route::get('my-profile/{vue_capture?}', [UserController::class, 'myProfile'])->name('my-profile')->where('vue_capture', '^(?!storage).*$');
        Route::get('my-info', [UserController::class, 'authData'])->name('authData');
        Route::post('my-profile/change-password', [UserController::class, 'change_password'])->name('change_password');
        Route::get('app-configuration', [App\Http\Controllers\Backend\API\SettingController::class, 'appConfiguraton']);
        Route::get('data-configuration', [App\Http\Controllers\Backend\API\SettingController::class, 'Configuraton']);


        Route::resource("mobile-setting", MobileSettingController::class);
        Route::group(['prefix' => 'mobile-setting', 'as' => 'mobile-setting.'], function () {
            Route::get('get-dropdown-value/{id}', [MobileSettingController::class, 'getDropdownValue'])->name('get-dropdown-value');

            Route::post('/mobile-setting/store', [MobileSettingController::class, 'store'])->name('storedata');
            Route::post('/mobile-setting/addnewrequest', [MobileSettingController::class, 'addNewRequest'])->name('addNewRequest');
            Route::post('/mobile-setting/addnewrequestsection', [MobileSettingController::class, 'addNewRequestSection'])->name('addNewRequestSection');
            Route::post('update-position', [MobileSettingController::class, 'updatePosition'])->name('update-position');
            Route::get('get-type-value/{slug}', [MobileSettingController::class, 'getTypeValue'])->name('get-type-value');
        });

    });

    Route::post('/auth/google', [SettingController::class, 'googleId']);
    Route::get('callback', [SettingController::class, 'handleGoogleCallback']);
    Route::post('/store-access-token', [SettingController::class, 'storeToken']);
    Route::get('google-key', [SettingController::class, 'googleKey']);
    Route::get('currencies_data', [SettingsController::class, 'getCurrencyData'])->name('backend.currencies.getCurrencyData');


    Route::group(['as' => 'backend.', 'middleware' => ['auth', 'admin', 'admin.timeout']], function () {
        Route::post('/clear-cache-config', function () {
            \Artisan::call('config:clear');
            \Artisan::call('cache:clear');
            return response()->json(['message' => 'Cache and Config cleared']);
        })->name('config_clear');
    });

});

Route::middleware(['web'])->group(function () {
    // Public routes
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');

    // Protected routes with auth
    Route::middleware(['auth'])->group(function () {
        Route::post('logout', 'Auth\LoginController@logout')->name('logout');
        // Other protected routes...
    });
});

});

// Partner Module Routes
require base_path('Modules/Partner/Routes/web.php');

// Journal d'audit
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'admin', 'admin.timeout']], function () {
    Route::get('audit-log', [App\Http\Controllers\Backend\AuditLogController::class, 'index'])->name('audit-log.index');
});

// ── Homepage Builder ──────────────────────────────────────────────────────────
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'admin', 'admin.timeout']], function () {
    Route::get('homepage-builder',                 [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'index'])->name('homepage-builder.index');
    Route::get('homepage-builder/create',          [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'create'])->name('homepage-builder.create');
    Route::get('homepage-builder/content-options', [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'getContentOptionsAjax'])->name('homepage-builder.content-options');
    Route::get('homepage-builder/tvshow-seasons',   [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'getTvshowSeasons'])->name('homepage-builder.tvshow-seasons');
    Route::get('homepage-builder/season-episodes',  [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'getSeasonEpisodes'])->name('homepage-builder.season-episodes');
    Route::post('homepage-builder/reorder',        [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'reorder'])->name('homepage-builder.reorder');
    Route::post('homepage-builder',                [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'store'])->name('homepage-builder.store');
    Route::get('homepage-builder/{id}/edit',       [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'edit'])->name('homepage-builder.edit');
    Route::put('homepage-builder/{id}',            [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'update'])->name('homepage-builder.update');
    Route::delete('homepage-builder/{id}',         [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'destroy'])->name('homepage-builder.destroy');
    Route::post('homepage-builder/{id}/toggle',    [\Modules\HomepageBuilder\Http\Controllers\Backend\HomepageBuilderController::class, 'toggleActive'])->name('homepage-builder.toggle');
});

