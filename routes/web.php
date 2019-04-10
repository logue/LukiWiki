<?php

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
// 管理
Route::get(':dashboard', 'DashboardController');
Route::get(':dashboard/convert', 'DashboardController@convert');
Route::post(':dashboard/convert', 'DashboardController@convert');
Route::get(':dashboard/clear-cache', 'DashboardController@clearCache');
Route::get(':dashboard/interwiki', 'DashboardController@interwiki');
Route::post(':dashboard/interwiki', 'DashboardController@interwiki');
Route::any(':dashboard/captcha-test', 'DashboardController@captchaTest');
Route::get(':dashboard/job', 'JobController@index');

// 認証系
Route::get(':login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post(':login', 'Auth\LoginController@login');
Route::post(':logout', 'Auth\LoginController@logout')->name('logout');
Route::get(':login/{social}', 'Auth\LoginController@socialLogin');
Route::get(':login/{social}/callback', 'Auth\LoginController@handleProviderCallback');

// 登録系
Route::get(':register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post(':register', 'Auth\RegisterController@register');

// パスワード初期化系
Route::get(':password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post(':password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get(':password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post(':password/reset', 'Auth\ResetPasswordController@reset');

// ユーザ管理
//Route::get(':dashboard/user', 'UserController@list');
//Route::post(':dashboard/user', 'UserController@store');
//Route::get(':dashboard/user/new', 'UserController@edit');
//Route::get(':dashboard/user/show/{id}', 'UserController@read');
//Route::get(':dashboard/user/edit/{id}', 'UserController@edit');
//Route::get(':dashboard/user/delete/{id}', 'UserController@destroy');

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
Route::get('{page?}{file?}', 'WikiController');
// ページ保存処理
Route::post('{page?}', 'WikiController@save')->middleware('sanitize');
// ページ削除処理
Route::post('{page}:delete', 'WikiController@destroy')->middleware('sanitize');
// ファイル添付処理
Route::post('{page}:upload', 'WikiController@upload');
