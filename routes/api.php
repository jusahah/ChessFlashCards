<?php

use App\ExternalApis\FiDomain\Real\FicoraClient;
use App\User;
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

/*
* V1 Api
*/
Route::namespace('Api')->prefix('v1')->group(function() {

    ////////////////////////////////////////////////////////////////////////////
    ///////////////////// UNAUTHED ROUTES //////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    Route::group(['middleware' => 'guest:api'], function () {
        // These drive the login flow for OAuth
        // Route::post('oauth/{driver}', 'OAuthController@redirectToProvider');
        //Route::get('oauth/{driver}/callback', 'OAuthController@handleProviderCallback')->name('oauth.callback');

        // Test route
        Route::get('guest-test', function() {
            return 'access successful';    
        });        
    });

    ////////////////////////////////////////////////////////////////////////////
    /////////////////////// AUTHED ROUTES //////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    Route::group(['middleware' => ['tokenauth']], function() {

        // Api key validation route, does nothing but return successful response
        Route::post('validatekey', 'AuthController@validateToken');
        Route::post('logout', 'AuthController@logout')->name('UserLogout');

        // Test route
        Route::get('test', function() {
            return \Auth::guard('api')->user()->getId();    
        })->name('AuthTestRoute');

        Route::post('upload', 'UploadGamesController@upload');
        Route::get('unprocessed-games', 'GamesController@unprocessedGames');
        Route::delete('unprocessed-games/{id}', 'GamesController@reject');

    });

});



