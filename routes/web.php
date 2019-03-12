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
Route::any(':search', 'WikiController@search')->middleware('sanitize', 'keyword');

Route::get('{page}:edit', 'WikiController@edit');
Route::get('{page}:attachments', 'WikiController@attachments');
Route::get('{page}:attachments/{file}', 'WikiController@attachments');
Route::post('{page}:upload', 'WikiController@upload');

Route::get('{page}:history', 'WikiController@history');
Route::get('{page}:history/{age}', 'WikiController@history');
Route::get('{page}:diff', 'WikiController@diff');

Route::get('{page}:source', 'WikiController@source');
Route::get('{page}:print', 'WikiController@print');

Route::post('{page?}', 'WikiController@save')->middleware('sanitize');
Route::post('{page}:delete', 'WikiController@destroy')->middleware('sanitize');
Route::get('{page?}{file?}', 'WikiController');
