<?php

namespace App\Http\Controllers;

use App\LukiWiki;
use Storage;

class WikiController extends Controller
{
    /**
     * Wikiを表示.
     *
     * @param string $page
     *
     * @return Response
     */
    public function __invoke($page = 'MainPage')
    {
        $filename = 'data/'.bin2hex($page).'.txt';

        if (!Storage::exists($filename)) {
            return \App::abort(404);
        }
        $content = Storage::get($filename);

        $obj = LukiWiki\Parser::factory($content);

        return view(
           'base',
           [
               'content' => $obj,
               'title'   => 'test',
           ]
        );
    }

    /**
     * Wikiのファイル一覧.
     *
     * @return Response
     */
    public function list()
    {
        $dir = 'data';
        $files = Storage::allFiles($dir);
        $ret = [];
        foreach ($files as $file) {
            $filename = current(
                explode('.',    // 拡張子を削除
                    ltrim($file, $dir.'/')  // パスを削除
                )
            );
            if (empty($filename)) {
                // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
                continue;
            }

            $ret[$file] = hex2bin($filename);
        }
        dd($ret);
    }
}
