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
//Route::get(':user', 'UserController@list');
//Route::post(':user', 'UserController@store');
//Route::get(':user/new', 'UserController@edit');
//Route::get(':user/show/{id}', 'UserController@read');
//Route::get(':user/edit/{id}', 'UserController@edit');
//Route::get(':user/delete/{id}', 'UserController@destroy');

// 動的ページ
//Route::get(':{action}', 'ActionController');

// Wikiページ
Route::get(':create', 'WikiController@create');
Route::get(':list', 'WikiController@list');
Route::get(':recent', 'WikiController@recent');
Route::any(':search', 'WikiController@search')->middleware('sanitize', 'keyword');

Route::get('{page}:edit', 'WikiController@edit');
Route::post('{page?}', 'WikiController@save')->middleware('sanitize');
Route::get('{page}:attachments', 'WikiController@attachments');
Route::get('{page}:attachments/{file}', 'WikiController@attachments');
Route::post('{page}:upload', 'WikiController@upload');
Route::post('{page}:delete', 'WikiController@destroy')->middleware('sanitize');

Route::get('{page}:history', 'WikiController@history');
Route::get('{page}:history/{age}', 'WikiController@history');
Route::get('{page}:diff', 'WikiController@diff');
Route::get('{page}:source', 'WikiController@source');
Route::get('{page}:print', 'WikiController@print');

Route::get('{page?}{file?}', 'WikiController');
