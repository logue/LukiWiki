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
Route::get(':admin', 'AdministratorController');
Route::get(':admin/convert', 'AdministratorController@convert');
Route::post(':admin/convert', 'AdministratorController@convert');
Route::get(':admin/clearCache', 'AdministratorController@clearCache');

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
Route::get('{page}:edit', 'WikiController@edit')->where('page', '.[^:]+');
Route::get('{page}:attachments', 'WikiController@attachments')->where('page', '.[^:]+');
Route::get('{page}:attachments/{file}', 'WikiController@attachments')->where('page', '.[^:]+');
Route::get('{page}:history', 'WikiController@history')->where('page', '.[^:]+');
Route::get('{page}:source', 'WikiController@source')->where('page', '.[^:]+');
Route::get('{page}:print', 'WikiController@print')->where('page', '.[^:]+');
Route::post('{page}:delete', 'WikiController@destroy')->where('page', '.[^:]+');
// LukiWikiでは:を含まないアドレスはすべてページ名として処理する。
// これらの行は必ず最後に入れること。
Route::post('{page?}', 'WikiController@save')->where('page', '.[^:]+');
Route::get('{page?}{file?}', 'WikiController')->where('page', '.[^:]+');
