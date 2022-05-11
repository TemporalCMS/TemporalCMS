<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'install'], function() {
    Route::get('/', ['uses' => 'InstallController@home']);
    Route::post('/check/licence', ['uses' => 'InstallController@step1']);
    Route::post('/valid/licence', ['uses' => 'InstallController@step1_valid']);
    Route::post('/check/database', ['uses' => 'InstallController@step2']);
    Route::post('/set/database', ['uses' => 'InstallController@step2_set']);
    Route::post('/create/admin', ['uses' => 'InstallController@step3']);
    Route::post('/last/config', ['uses' => 'InstallController@step4']);
});