<?php
/**
 * ダッシュボード面コントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\User;
use Cache;
use Illuminate\Http\Request;
use Storage;

class DashboardController extends Controller
{
    const DEFAULT_PATH = ':dashboard';

    public function __construct()
    {
        // 認証用ミドルウェア
        //$this->middleware('auth');
    }

    /**
     * 管理トップページ.
     *
     * @return Illuminate\View\View
     */
    public function __invoke()
    {
        return view('dashboard/index', ['title'=>'Administrator']);
    }

    /**
     * ユーザ一覧.
     *
     * @return Illuminate\View\View
     */
    public function user(Request $request)
    {
        $users = User::paginate(15);

        return view('dashboard/users', ['title'=>'User List']);
    }

    /**
     * WikiデータをLukiWiki形式に変換.
     *
     * @return Illuminate\View\View
     */
    public function convert(Request $request)
    {
        if ($request->isMethod('post')) {
            $path = $request->input('path');

            if (\Storage::exists($path)) {
                switch ($request->input('type')) {
                    case 'attach':
                        $request->session()->flash('message', '添付ファイルのインポートのキューを実行しました。');
                        foreach (Storage::files($path.'/attach/') as $file) {
                            try {
                                $this->dispatch(new \App\Jobs\ProcessAttachmentData($file));
                            } catch (\Exception $e) {
                                Log::error('An error has occurred at '.$file);
                            }
                        }
                        break;
                    case 'backup':
                        $request->session()->flash('message', 'バックアップのインポートのキューを実行しました。');
                        foreach (Storage::files($path.'/backup/') as $file) {
                            try {
                                $this->dispatch(new \App\Jobs\ProcessBackupData($file));
                            } catch (\Exception $e) {
                                Log::error('An error has occurred at '.$file);
                            }
                        }
                        break;
                    case 'counter':
                        $request->session()->flash('message', 'カウンターのインポートのキューを実行しました。');
                        foreach (Storage::files($path.'/counter/') as $file) {
                            try {
                                $this->dispatch(new \App\Jobs\ProcessCounterData($file));
                            } catch (\Exception $e) {
                                Log::error('An error has occurred at '.$file);
                            }
                        }
                        break;
                    case 'wiki':
                        $request->session()->flash('message', 'Wikiデータのインポートのキューを実行しました。');
                        foreach (Storage::files($path.'/wiki/') as $file) {
                            try {
                                $this->dispatch(new \App\Jobs\ProcessWikiData($file));
                            } catch (\Exception $e) {
                                Log::error('An error has occurred at '.$file);
                            }
                        }
                        break;
                    default:
                        $request->session()->flash('message', 'キューの実行をキャンセルしました。');

                }
            } else {
                $request->session()->flash('message', 'ディレクトリが見つかりません。');
            }

            return redirect(':dashboard/convert');
        }

        return view('dashboard/convert', ['title'=>'Convert PukiWiki data.']);
    }

    /**
     * キャッシュクリア.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\View\View
     */
    public function clearCache(Request $request)
    {
        $args = $request->input('cache');
        $msg = [];

        if (count($args) === 0) {
            $request->session()->flash('message', '何も選択されていないため処理を中断しました。');

            return redirect(self::DEFAULT_PATH);
        }

        foreach ($args as $cache) {
            switch ($cache) {
                case 'view':
                    $files = glob(storage_path('framework/views').'/*.php');
                    foreach ($files as $no=>$file) {
                        unlink($file);
                    }
                    $msg[] = 'ビューキャッシュ';
                    break;
                case 'debug':
                    $files = glob(storage_path('framework/debugbar').'/*.json');
                    $msg[] = 'デバッグ情報';
                    break;
                case 'system':
                    Cache::flush();
                    $msg[] = 'システムキャッシュ';
                    break;
            }
        }

        $request->session()->flash('message', '以下のキャッシュを削除しました：<br />'.implode("<br />\n", $msg));

        return redirect(self::DEFAULT_PATH);
    }

    /**
     * InterWiki登録.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\View\View
     */
    public function interwiki(Request $request) :View
    {
    }
}
