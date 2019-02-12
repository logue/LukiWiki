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
use Illuminate\View\View;

class WikiController extends Controller
{
    // 新旧のデーターの比較に用いる要約用のハッシュのアルゴリズム
    // 使用可能な値：http://php.net/manual/ja/function.hash-algos.php
    // あくまでも新旧のデーターに変化があったかのチェック用途であるため、高速なcrc32で十分だと思う。
    const HASH_ALGORITHM = 'crc32';

    /**
     * コンストラクタ
     */
    public function __construct(Request $request)
    {
        // 設定読み込み
        $this->config = Config::get('lukiwiki');
    }

    /**
     * ページを読み込む
     */
    public function __invoke(Request $request, string $query):View
    {
        $page = rawurldecode($query);

        Debugbar::startMeasure('db', 'Get data from db.');

        //dd($page_obj->attachments()->where('name', '=', $page)->get());

        $ext = substr($query, strrpos($query, '.', -1), strlen($query));

        if (empty($page)) {
            $page = Config::get('lukiwiki.special_page.default');
        }
        $id = Page::getPageId($page);
        Debugbar::stopMeasure('db');
        if (!$id) {
            // ページが見つからない場合は404エラー
            //dd($page);
            return abort(404);
        }
        $entry = Page::find($id);

        Debugbar::startMeasure('parse', 'Converting wiki data...');
        //dd($entry->source);
        $lines = explode("\n", str_replace([chr(0x0d).chr(0x0a), chr(0x0d), chr(0x0a)], "\n", $entry->source));

        $body = new RootElement($page, 0, ['id' => 0]);

        $meta = $body->getMeta();
        $body->parse($lines);
        Debugbar::stopMeasure('parse');

        return view(
           'default.content',
           [
                'page'      => $page,
                'content'   => $body->__toString(),
                'title'     => $entry->title ?? $page,
                'notes'     => $meta['note'] ?? null,
                'attaches'  => $entry->attachments()->get(),
            ]
        );
    }

    /**
     * ソース.
     */
    public function source(Request $request, string $page):View
    {
        $entry = Page::where('name', $page)->first();

        return view(
           'default.source', [
                'page'    => $entry->name,
                'source'  => $entry->source,
                'title'   => 'Source of '.$entry->name,
            ]
        );
    }

    /**
     * 添付ファイル一覧.
     */
    public function attachments(Request $request, string $page, ?string $file = null)
    {
        $attachments = Page::getAttachments($page);

        if (!empty($file)) {
            //dd($attachments->where('attachments.name', $file)->first()->id);
            return redirect(':api/attachment/'.$attachments->where('attachments.name', $file)->first()->id);
        }

        return view(
           'default.attachment', [
                'page'         => $page,
                'attachments'  => $attachments->select('attachments.*')->get(),
                'title'        => 'Attached files of '.$page,
            ]
        );
    }

    /**
     * 印刷.
     */
    public function print(Request $request, string $page):View
    {
        $entry = Page::where('name', $page)->first();

        Debugbar::startMeasure('parse', 'Converting wiki data...');
        //dd($entry->source);
        $lines = explode("\n", str_replace([chr(0x0d).chr(0x0a), chr(0x0d), chr(0x0a)], "\n", $entry->source));

        $body = new RootElement($page, 0, ['id' => 0]);

        $meta = $body->getMeta();
        $body->parse($lines);
        Debugbar::stopMeasure('parse');

        return view(
           'layout.print', [
                'page'    => $entry->name,
                'body'    => $body,
                'title'   => $entry->name,
            ]
        );
    }

    /**
     * 編集画面表示.
     */
    public function edit(Request $request, string $page = null):View
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
     * ページ一覧.
     *
     * @param string $type
     *
     * @return View
     */
    public function list():View
    {
        return view(
            'default.list',
            [
                'entries' => Page::getEntries(),
                'title'   => 'List',
            ]
        );
    }

    /**
     * ページ一覧.
     *
     * @param string $type
     *
     * @return View
     */
    public function recent():View
    {
        return view(
            'default.recent',
            [
                'entries' => Page::getLatest(),
                'title'   => 'RecentChanges',
            ]
        );
    }
}
