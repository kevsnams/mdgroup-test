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



Route::namespace('api')->name('api.')->group(function () {

    Route::prefix('api')->group(function () {
        Route::get('/exchange/{amount}/{from}/{to}', 'ExchangeController@convert');
        Route::get('/exchange/info', 'ExchangeController@info');

        Route::get('/cache/clear', 'ExchangeController@cacheClear');
    });

    Route::fallback('ExchangeController@fallback');
});

