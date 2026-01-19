<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WikiController;
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

// 管理
Route::prefix(':dashboard')->group(function () {
    // 管理用トップページ
    Route::get('/', [DashboardController::class, 'index'])->name('page');
    // データー変換処理
    Route::get('/convert', [DashboardController::class, 'convert']);
    Route::post('/convert', [DashboardController::class, 'convert']);
    // キャッシュ処理
    Route::post('/clear-cache', [DashboardController::class, 'clearCache']);
    // InterWikiNameの管理
    Route::get('/interwiki', [DashboardController::class, 'interwiki']);
    Route::post('/interwiki', [DashboardController::class, 'interwiki']);
    // CAPTCHA認証テスト
    Route::any('/captcha-test', [DashboardController::class, 'captchaTest']);
    // ジョブ管理
    Route::get('/job', [JobController::class, 'index']);
    // ユーザ個人管理
});

// 認証系のルーティング
// Auth::routes();
// 登録系
Route::prefix(':auth')->group(function () {
    // ログイン
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    // ログアウト
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    // 登録
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    // パスワード初期化
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset']);
});

// ユーザ管理
Route::prefix(':user')->group(function () {
    Route::get('/', [UserController::class, 'list']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'read']);
    Route::get('/edit/{id}', [UserController::class, 'edit']);
    Route::get('/delete/{id}', [UserController::class, 'destroy']);
});

// 検索処理
Route::any(':search', [WikiController::class, 'search'])->middleware('sanitize', 'keyword');
// 記事一覧
Route::get(':list', [WikiController::class, 'list']);
// 記事作成
Route::get(':new', [WikiController::class, 'edit']);
// 最新記事
Route::get(':recent', [WikiController::class, 'recent']);
// 添付一覧
Route::get('{page}:attachments', [WikiController::class, 'attachments']);
// 添付ファイル
Route::get('{page}:attachments/{file}', [WikiController::class, 'attachments']);
// 差分
Route::get('{page}:diff', [WikiController::class, 'diff']);
// 編集
Route::get('{page}:edit', [WikiController::class, 'edit']);
// バックアップ一覧
Route::get('{page}:history', [WikiController::class, 'history']);
// バックアップの世代表示
Route::get('{page}:history/{age}', [WikiController::class, 'history']);
// バックアップの世代の今との差分
Route::get('{page}:history/{age}:diff', [WikiController::class, 'history']);
// バックアップのロールバック
Route::get('{page}:history/{age}:rollback', [WikiController::class, 'history']);
// 印刷
Route::get('{page}:print', [WikiController::class, 'print']);
// ソース
Route::get('{page}:source', [WikiController::class, 'source']);
// ページ表示
Route::get('{page?}{file?}', [WikiController::class, '__invoke'])->name('page');
// ページ保存処理
Route::post('{page?}', [WikiController::class, 'save'])->middleware('sanitize');
// ページ削除処理
Route::post('{page}:delete', [WikiController::class, 'destroy'])->middleware('sanitize');
// ファイル添付処理
Route::post('{page}:upload', [WikiController::class, 'upload']);
