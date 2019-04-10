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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('atom', 'ApiController@atom');
Route::get('sitemap', 'ApiController@sitemap');
Route::get('opensearch', 'ApiController@opensearch');
Route::get('attachment/{id}', 'ApiController@attachment');
// プラグインのAPI
Route::any('{page}:{name}', 'ApiController@plugin')->middleware('sanitize');
