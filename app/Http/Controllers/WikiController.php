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
use App\LukiWiki\Utility\WikiFileSystem;
use Config;
use Illuminate\Http\Request;

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
        $this->data = new WikiFileSystem('data');
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
        $this->exists = isset($this->data->$page);
        if ($this->exists) {
            $this->content = $this->data->$page;
        }

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

        if (!$this->exists) {
            return \App::abort(404);
        }

        return $this->read();
    }

    /**
     * ページを読み込む
     */
    private function read()
    {
        if (!$this->exists) {
            return \App::abort(404);
        }

        return view(
           'default.content',
           [
                'page' => $this->page,
                'content' => Parser::factory($this->content),
                'title' => $this->page,
           ]
        );
    }

    /**
     * AMP用ページ出力.
     */
    private function amp()
    {
        if (!$this->exists) {
            return \App::abort(404);
        }
        
        \Debugbar::disable();

        return view(
           'default.amp',
           [
                'page' => $this->page,
                'content' => Parser::factory($this->content, true),
                'title' => $this->page,
           ]
        );
    }

    /**
     * 編集画面表示.
     */
    private function edit()
    {
        if (!$this->exists) {
            // TODO
            return \App::abort(404);
        }

        return view(
            'default.edit',
            [
                'page' => $this->page,
                'source' => $this->content,
                'title' => 'Edit '.$this->page,
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
        $filelist = $this->data;

        return view(
            'default.list',
            [
                'pages' => array_keys($filelist()),
                'title' => 'List',
            ]
         );
    }
}
