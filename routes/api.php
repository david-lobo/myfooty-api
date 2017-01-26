<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$url = config('app.url');
$domain = str_replace("http://", "", $url);

Route::group(['domain' => "api.{$domain}"], function () {

    Route::get('/config/{env}', 'Api\ConfigController');

    Route::get('/mock/fixtures', 'Mock\ApiController@fixtures');
    Route::get('/mock/broadcasting-schedule/fixtures', 'Mock\ApiController@broadcastingSchedule');

    Route::group(['prefix' => 'v1'], function () {
        Route::get('/clubs', 'Api\ClubController@index');
        Route::get('/clubs/{id}', 'Api\ClubController@show');
        Route::get('/fixtures', 'Api\FixtureController@index');

        // Auth routes
        Route::resource('authenticate', 'AuthenticateController', ['only' => ['index']]);
        //Route::post('authenticate/login', 'AuthenticateController@login');
        //Route::post('authenticate/register', 'AuthenticateController@register');
        Route::get('authenticate/user', 'AuthenticateController@getAuthenticatedUser');
        Route::post('authenticate/register-device', 'AuthenticateController@registerDevice');
        Route::resource('users', 'Api\UserController');

    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group(['domain' => "api.{$domain}"], function () {

Route::get('/mock/fixtures', 'Mock\ApiController@fixtures');
Route::get('/mock/broadcasting-schedule/fixtures', 'Mock\ApiController@broadcastingSchedule');
});
