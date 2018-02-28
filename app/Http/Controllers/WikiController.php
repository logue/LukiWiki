<?php
/**
 * LukiWikiコントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\LukiWiki\Parser;
use App\LukiWiki\Utility\FileList;
use Config;
use Illuminate\Http\Request;
use Storage;

class WikiController extends Controller
{
    // 設定
    private $config = [];
    // ページ名
    private $page = null;

    /**
     * コンストラクタ
     */
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
        $this->filename = FileList::encode($this->page).'.txt';
        switch ($request->input('action')) {
            case 'edit':
                return $this->edit();
                break;
            case 'amp':
                return $this->amp();
                break;
            case 'attach':
                return $this->attach();
                break;
            case 'list':
                return $this->list($request->input('type') ?? 'data');
                break;
            case 'backup':
                return $this->backup();
                break;
            case 'lock':
                return $this->lock();
                break;
        }

        return $this->read();
    }

    /**
     * ページを読み込む
     */
    private function read()
    {
        $filename = $this->config['directory']['data'].'/'.$this->filename;

        if (!Storage::exists($filename)) {
            return \App::abort(404);
        }
        $content = Storage::get($filename);

        $obj = Parser::factory($content);

        return view(
           'default.content',
           [
                'page'    => $this->page,
                'content' => $obj,
                'title'   => $this->page,
           ]
        );
    }

    /**
     * AMP用ページ出力.
     */
    private function amp()
    {
        $filename = $this->config['directory']['data'].'/'.$this->filename;

        if (!Storage::exists($filename)) {
            return \App::abort(404);
        }
        $content = Storage::get($filename);

        $obj = Parser::factory($content, true);

        return view(
           'default.amp',
           [
                'page'    => $this->page,
                'content' => $obj,
                'title'   => $this->page,
           ]
        );
    }

    /**
     * 編集画面表示.
     */
    private function edit()
    {
        $filename = $this->config['directory']['data'].'/'.$this->filename;

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

        $filelist = new FileList($this->config['directory']);

        return view(
            'default.list',
            [
                'pages' => array_keys($filelist->getList($type)),
                'title' => 'List',
            ]
         );
    }
}
