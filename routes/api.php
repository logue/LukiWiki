<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 最新記事
Route::get('atom', [ApiController::class, 'atom']);
// サイトマップ
Route::get('sitemap', [ApiController::class, 'sitemap']);
// OpenSearch
Route::get('opensearch', [ApiController::class, 'opensearch']);
// 添付ファイル
Route::get('attachment/{id}', [ApiController::class, 'attachment']);
// 指定ページ階層以下の一覧
Route::get('list:{page}', [ApiController::class, 'list']);
// プラグインのAPI
Route::any('{name}:{page}', [ApiController::class, 'plugin'])->middleware('sanitize');
// パッケージ一覧
Route::get('composer', 'ComposerController@index');
// Composer実行
Route::post('composer', 'ComposerController@execute');
