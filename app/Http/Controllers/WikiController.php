<?php

namespace App\Http\Controllers;

use App\LukiWiki;
use Config;
use Illuminate\Http\Request;
use Storage;

class WikiController extends Controller
{
    public function __construct()
    {
        // 設定読み込み
        $this->config = Config::get('lukiwiki');
    }

    /**
     * Wikiを表示.
     *
     * @param Request $request
     * @param string  $page    ページ名
     *
     * @return Response
     */
    public function __invoke(Request $request, $page = 'MainPage')
    {
        $this->page = $page;
        switch ($request->input('action')) {
            case 'edit':
                return $this->edit();
                break;
            case 'attach':
                return $this->attach();
                break;
            case 'list':
                return $this->list($request->input('type') ?? 'data');
                break;
        }

        return $this->read();
    }

    /**
     * ページを読み込む
     */
    private function read()
    {
        $filename = $this->config['directory']['data'].'/'.strtoupper(bin2hex($this->page)).'.txt';

        if (!Storage::exists($filename)) {
            return \App::abort(404);
        }
        $content = Storage::get($filename);

        $obj = LukiWiki\Parser::factory($content);

        return view(
           'default.content',
           [
                'page'    => $this->page,
                'content' => $obj,
                'title'   => $this->page,
           ]
        );
    }

    private function edit()
    {
        $filename = $this->config['directory']['data'].'/'.strtoupper(bin2hex($this->page)).'.txt';

        if (!Storage::exists($filename)) {
            return \App::abort(404);
        }

        return view(
            'default.edit',
            [
                'page'   => $this->page,
                'source' => Storage::get($filename),
                'title'  => 'Edit',
            ]
         );
    }

    /**
     * ファイル一覧.
     *
     * @param string $type
     *
     * @return Response
     */
    public function list($type)
    {
        if (!isset($this->config['directory'][$type])) {
            return \App::abort('not mounted');
        }
        $dir = $this->config['directory'][$type];
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

        return view(
            'default.list',
            [
                'pages' => $ret,
                'title' => 'List',
            ]
         );
    }
}
