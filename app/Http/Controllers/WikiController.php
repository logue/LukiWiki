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
use Config;
use Debugbar;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    // 設定
    private $config = [];
    // ページ名
    private $page = null;

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
     * Wikiを表示.
     *
     * @param Request $request
     * @param string  $page    ページ名
     *
     * @return Response
     */
    public function __invoke(Request $request, $page = null, $action = null)
    {
        $this->page = $page;
        $this->request = $request;

        switch ($action) {
            case 'new':
            case 'edit':
                return $this->edit();
                break;
            case 'save':
                return $this->save();
                break;
            case 'attachment':
                return $this->attachment();
                break;
            case 'history':
                return $this->history();
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
                return $this->list();
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
                    ->view('api.atom', ['entries' => $this->getLatest()])
                    ->header('Content-Type', ' application/xml; charset=UTF-8');
                break;
            case 'sitemap':
                // Sitemap
                return response()
                    ->view('api.sitemap', ['entries' => $filelist()])
                    ->header('Content-Type', ' application/xml; charset=UTF-8');
                break;
            case 'cancel':
                return redirect($request->input('page'));
                break;
            case 'amp':
                return $this->read(true);
                break;
            default:
                return $this->read(false);
                break;
        }
    }

    /**
     * ページを読み込む
     */
    public function read($page = null)
    {
        $data = $this->data->$page;

        if (!$data) {
            // ページが見つからない場合は404エラー
            return abort(404);
        }

        Debugbar::startMeasure('parse', 'Converting wiki data...');

        $lines = explode("\n", str_replace([chr(0x0d).chr(0x0a), chr(0x0d), chr(0x0a)], "\n", $data));

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
     * 編集画面表示.
     */
    public function edit($page = null)
    {
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

        $data = $this->data->$page;

        return view(
            'default.edit',
            [
                'page'   => $page,
                'source' => $data,
                'title'  => 'Edit '.$page,
                'hash'   => hash(self::HASH_ALGORITHM, $data),
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
    public function save()
    {
        if (!$this->request->isMethod('post')) {
            // Method not allowed
            abort(405);
        }

        $page = $this->request->input('page');

        if (empty($page)) {
            abort(400);
        }

        $data = $this->data->$page;

        if (hash(self::HASH_ALGORITHM, $data) !== $this->request->input('hash')) {
            // 編集中に別の人が編集をした（競合をおこした）

            // TODO: この処理は動かない。3way-diffを出力
            $merger = new PhpMerge\PhpMerge();
            $result = $merger->merge(
                $this->request->input('original'),
                $data,
                $this->request->input('source')
            );
            dd($result);

            return view(
                'default.conflict',
                [
                    'page'   => $page,
                    'diff'   => $result,
                    'source' => $this->request->input('source'),
                    'title'  => 'Conflict '.$page,
                    'hash'   => hash(self::HASH_ALGORITHM, $data),
                ]
            );
        }

        // 保存処理
        //dd($page, $this->request->input('source'));
        // $this->data->$page = $this->request->input('source'); ←動かない（マジックメソッドが使えない）
        $this->data->__set($page, $this->request->input('source'));

        // TODO:バックアップ処理

        $this->request->session()->flash('message', 'Saved');

        return redirect($page);
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
            ++$i;
        }
        array_multisort($entries, SORT_DESC, $modified);

        return $entries;
    }
}
