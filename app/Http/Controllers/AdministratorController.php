<?php
/**
 * 管理画面コントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\LukiWiki\Utility\Converter;
use App\User;
use Cache;
use Illuminate\Http\Request;

class AdministratorController extends Controller
{
    const DEFAULT_PATH = ':admin';

    /*
     * 管理トップページ
     */
    public function __invoke()
    {
        return view('admin/index', ['title'=>'Administrator']);
    }

    /**
     * ユーザ一覧.
     */
    public function user(Request $request)
    {
        $users = User::paginate(15);

        return view('admin/users', ['title'=>'User List']);
    }

    /**
     * WikiデータをLukiWiki形式に変換.
     */
    public function convert(Request $request)
    {
        if ($request->isMethod('post')) {
            // Method not allowed
            $converter = new Converter($request->input('path'));

            switch ($request->input('type')) {
                case 'wiki':
                   return $converter->wiki();
                   break;
            }
        }

        return view('admin/convert', ['title'=>'Convert PukiWiki data.']);
    }

    /**
     * キャッシュクリア.
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
}
