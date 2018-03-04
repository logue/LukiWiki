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
        $this->data = WikiFileSystem::getInstance();
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
        $this->content = $this->data->$page;

        switch ($request->input('action')) {
            case 'edit':
                return $this->edit();
                break;
            case 'amp':
                return $this->amp();
                break;
            case 'attachment':
                return $this->attachment();
                break;
            case 'backup':
                return $this->backup();
                break;
            case 'source':
                return view(
                   'default.source',
                   [
                       'source' => $this->data->$page,
                       'title'  => 'Source of '.$page,
                       'page'   => $page,
                   ]
               );
            case 'list':
                return $this->list($request->input('type') ?? 'data');
                break;
            case 'lock':
                return $this->lock();
                break;
            case 'recent':
               // 最終更新
               return view(
                   'default.recent',
                   [
                       'entries' => $this->getLatest(),
                       'title'   => 'RecentChanges',
                   ]
               );
               break;
            case 'atom':
                // ATOM
                return response()
                    ->view('api.atom', ['entries'=>$this->getLatest()])
                    ->header('Content-Type', ' application/xml; charset=UTF-8');
                break;
            case 'sitemap':
                // Sitemap
                return response()
                    ->view('api.sitemap', ['entries'=>$filelist()])
                    ->header('Content-Type', ' application/xml; charset=UTF-8');
                break;
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
                'page'    => $this->page,
                'content' => Parser::factory($this->content),
                'title'   => $this->page,
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
                'page'    => $this->page,
                'content' => Parser::factory($this->content, true),
                'title'   => $this->page,
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
                'page'   => $this->page,
                'source' => $this->content,
                'title'  => 'Edit '.$this->page,
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
        // 全ファイル一覧（WikiFileSystemオブジェクト）
        $filelist = $this->data;
        $entries = [];

        return view(
            'default.list',
            [
                'entries' => $filelist(),
                'title'   => 'List',
            ]
        );
    }

    /**
     * ページ一覧を新しい順に並び替える.
     *
     * @param int $limit 制限件数
     *
     * @return array
     */
    private function getLatest($limit = 10)
    {
        $data = $this->data;
        $entries = $data();
        $i = 0;
        foreach ($entries as $key => $value) {
            if ($i === $limit) {
                break;
            }
            $modified[$key] = $value['timestamp'];
            $i++;
        }
        array_multisort($entries, SORT_DESC, $modified);

        return $entries;
    }
}
