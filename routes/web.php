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

/*
// 管理
Route::prefix(':dashboard')->group(function () {
    // 管理用トップページ
    Route::get('/', 'DashboardController')->name('page');
    // データー変換処理
    Route::get('/convert', 'DashboardController@convert');
    Route::post('/convert', 'DashboardController@convert');
    // キャッシュ処理
    Route::get('/clear-cache', 'DashboardController@clearCache');
    // InterWikiNameの管理
    Route::get('/interwiki', 'DashboardController@interwiki');
    Route::post('/interwiki', 'DashboardController@interwiki');
    // CAPTCHA認証テスト
    Route::any('/captcha-test', 'DashboardController@captchaTest');
    // ジョブ管理
    Route::get('/job', 'JobController@index');
    // ユーザ個人管理
});
*/
// 認証系のルーティング
//Auth::routes();
// 登録系
Route::prefix(':auth')->group(function () {
    // ログイン
    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'Auth\LoginController@login');
    // ログアウト
    Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
    // 登録
    Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('/register', 'Auth\RegisterController@register');
    // パスワード初期化
    Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('/password/reset', 'Auth\ResetPasswordController@reset');
});

// ユーザ管理
Route::prefix(':user')->group(function () {
    Route::get('/', 'UserController@list');
    Route::post('/', 'UserController@store');
    Route::get('/{id}', 'UserController@read');
    Route::get('/edit/{id}', 'UserController@edit');
    Route::get('/delete/{id}', 'UserController@destroy');
});

// 検索処理
Route::any(':search', 'WikiController@search')->middleware('sanitize', 'keyword');
// 記事一覧
Route::get(':list', 'WikiController@list');
// 記事作成
Route::get(':new', 'WikiController@edit');
// 最新記事
Route::get(':recent', 'WikiController@recent');
// 添付一覧
Route::get('{page}:attachments', 'WikiController@attachments');
// 添付ファイル
Route::get('{page}:attachments/{file}', 'WikiController@attachments');
// 差分
Route::get('{page}:diff', 'WikiController@diff');
// 編集
Route::get('{page}:edit', 'WikiController@edit');
// バックアップ一覧
Route::get('{page}:history', 'WikiController@history');
// バックアップの世代表示
Route::get('{page}:history/{age}', 'WikiController@history');
// バックアップの世代の今との差分
Route::get('{page}:history/{age}:diff', 'WikiController@history');
// バックアップのロールバック
Route::get('{page}:history/{age}:rollback', 'WikiController@history');
// 印刷
Route::get('{page}:print', 'WikiController@print');
// ソース
Route::get('{page}:source', 'WikiController@source');
// ページ表示
Route::get('{page?}{file?}', 'WikiController')->name('page');
// ページ保存処理
Route::post('{page?}', 'WikiController@save')->middleware('sanitize');
// ページ削除処理
Route::post('{page}:delete', 'WikiController@destroy')->middleware('sanitize');
// ファイル添付処理
Route::post('{page}:upload', 'WikiController@upload');
