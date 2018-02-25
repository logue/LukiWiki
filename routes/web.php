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

// ファイル一覧
Route::get('/:list', 'WikiController@list');
// AMP対応版
Route::get('/:amp/{page?}', 'WikiController@amp');
// 通常時の処理
Route::get('/{page?}', 'WikiController');
