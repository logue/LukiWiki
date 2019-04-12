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

// 最新記事
Route::get('atom', 'ApiController@atom');
// サイトマップ
Route::get('sitemap', 'ApiController@sitemap');
// OpenSearch
Route::get('opensearch', 'ApiController@opensearch');
// 添付ファイル
Route::get('attachment/{id}', 'ApiController@attachment');
// 指定ページ階層以下の一覧
Route::get('list:{page}', 'ApiController@list');
// プラグインのAPI
Route::any('{name}:{page}', 'ApiController@plugin')->middleware('sanitize');
