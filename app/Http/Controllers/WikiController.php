<?php
/**
 * LukiWikiコントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018-2019 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\LukiWiki\Element\RootElement;
use App\Models\Page;
use Config;
use Debugbar;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    // 新旧のデーターの比較に用いる要約用のハッシュのアルゴリズム
    // 使用可能な値：http://php.net/manual/ja/function.hash-algos.php
    // あくまでも新旧のデーターに変化があったかのチェック用途であるため、高速なcrc32で十分だと思う。
    const HASH_ALGORITHM = 'crc32';

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // 設定読み込み
        $this->config = Config::get('lukiwiki');
    }

    /**
     * ページを読み込む
     */
    public function read(Request $request, $page = null)
    {
        Debugbar::startMeasure('db', 'Get data from db.');
        $entry = Page::where('name', $page ?? $request->input('page') ?? Config::get('lukiwiki.special_page.default'))->first();
        Debugbar::stopMeasure('db');
        if (!$entry) {
            // ページが見つからない場合は404エラー
            return abort(404);
        }

        Debugbar::startMeasure('parse', 'Converting wiki data...');
        $lines = explode("\n", str_replace([chr(0x0d).chr(0x0a), chr(0x0d), chr(0x0a)], "\n", $entry->source));

        $body = new RootElement('', 0, ['id' => 0]);
        $body->parse($lines);
        $meta = $body->getMeta();
        Debugbar::stopMeasure('parse');

        return view(
           'default.content',
           [
                'page'    => $page,
                'content' => $body,
                'title'   => $meta['title'] ?? $page,
                'notes'   => $meta['note'] ?? null,
            ]
        );
    }

    /**
     * ソース.
     */
    public function source(Request $request, $page = null)
    {
        $entry = Page::where('name', $page)->first();

        return view(
           'default.source', [
           'page'         => $entry->name,
                'source'  => $entry->source,
                'title'   => $entry->title,
            ]
        );
    }

    /**
     * 編集画面表示.
     */
    public function edit(Request $request, $page = null)
    {
        $this->page = $page ?? $request->input('page') ?? Config::get('lukiwiki.special_page.default');

        if (!$page) {
            // 新規ページ
            return view(
                'default.edit',
                [
                    'page'   => '',
                    'source' => '',
                    'title'  => 'Create New Page',
                    'hash'   => 0,
                ]
             );
        }

        $entry = Page::where('name', $this->page)->first();

        return view(
            'default.edit',
            [
                'page'        => $page,
                'source'      => $entry->source,
                'description' => $entry->description,
                'title'       => 'Edit '.$page,
                'hash'        => hash(self::HASH_ALGORITHM, $entry->source),
            ]
         );
    }

    /**
     * 保存処理.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function save(Request $request)
    {
        if (!$request->isMethod('post')) {
            // Method not allowed
            abort(405);
        }

        $page = $request->input('page');

        if (empty($page)) {
            abort(400);
        }

        $entry = Page::where('name', $this->page)->first();

        if (hash(self::HASH_ALGORITHM, $data) !== $request->input('hash')) {
            // 編集中に別の人が編集をした（競合をおこした）

            // TODO: この処理は動かない。3way-diffを出力
            $merger = new PhpMerge\PhpMerge();
            $result = $merger->merge(
                $request->input('original'),
                $data,
                $request->input('source')
            );
            dd($result);

            return view(
                'default.conflict',
                [
                    'page'   => $page,
                    'diff'   => $result,
                    'source' => $equest->input('source'),
                    'title'  => 'Conflict '.$page,
                    'hash'   => hash(self::HASH_ALGORITHM, $data),
                ]
            );
        }

        // 保存処理
        //dd($page, $this->request->input('source'));
        // $this->data->$page = $this->request->input('source'); ←動かない（マジックメソッドが使えない）
        //$this->data->__set($page, $this->request->input('source'));

        // TODO:バックアップ処理

        $request->session()->flash('message', 'Saved');

        return redirect($page);
    }

    /**
     * ファイル一覧.
     *
     * @param string $type
     *
     * @return Response
     */
    public function list()
    {
        $pages = Page::getEntries();

        return view(
            'default.list',
            [
                'entries' => $pages,
                'title'   => 'List',
            ]
        );
    }
}
