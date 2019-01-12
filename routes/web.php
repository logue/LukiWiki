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

// LukiWikiでは原則的にすべてページ名として処理し、ルーティングはURLクエリで処理するため、
// ここにルーティングを書かない。
Route::any('/{page?}', 'WikiController');
