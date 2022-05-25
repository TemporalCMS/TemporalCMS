<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', ['has' => 'CMSHomePage', 'uses' => 'HomeController@index']);
Route::post('/load/read/all/notifications', ['uses' => 'HomeController@ajax_read_all_notifications']);
Route::post('/load/read/notification', ['uses' => 'HomeController@ajax_read_notification']);

Route::get('/get/lang/datatable', ['uses' => 'Controller@datatable_lang']);

Route::get('/storage/public/{file}', ['uses' => 'Controller@storage_public_file']);
Route::get('/storage/public/avatar/{file}', ['uses' => 'Controller@storage_public_avatar_file']);

Route::group(['namespace' => 'Assets', 'prefix' => 'assets'], function() {
    Route::get('plugin/public/{folder}/{file}', ['uses' => 'PluginController@assets', 'as' => 'assets.plugin.public.file']);
    Route::get('theme/public/{file}', ['uses' => 'ThemeController@assets', 'as' => 'assets.theme.public.file']);
    Route::get('game/public/{file}', ['uses' => 'GameController@assets', 'as' => 'assets.game.public.file']);
});

Route::get('/admin/{any?}', [App\Http\Controllers\DashboardController::class, 'index'])->where('any', '.*');

Route::group(['prefix' => 'p', 'namespace' => 'Component\App'], function() {
    Route::get('/{slug}', ['as' => 'CMSPagePage', 'uses' => 'PageController@home']);
});


