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
use App\Models\Attachment;
use App\Models\Backup;
use App\Models\Page;
use Carbon\Carbon;
use Config;
use Debugbar;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use SebastianBergmann\Diff\Differ;

class WikiController extends Controller
{
    // 新旧のデーターの比較に用いる要約用のハッシュのアルゴリズム
    // 使用可能な値：http://php.net/manual/ja/function.hash-algos.php
    // あくまでも新旧のデーターに変化があったかのチェック用途であるため、高速なcrc32で十分だと思う。
    const HASH_ALGORITHM = 'crc32';

    public function __construct()
    {
        $this->page = new Page();
    }

    /**
     * ページを読み込む
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $query
     *
     * @return Illuminate\View\View
     */
    public function __invoke(Request $request, ?string $query = null):View
    {
        $page = rawurldecode($query);

        if (empty($page)) {
            $page = Config::get('lukiwiki.special_page.default');
        }

        Debugbar::startMeasure('db', 'Fetch '.$page.' data from db.');
        $entry = $this->page->where('name', $page)->first();
        if (!$entry) {
            return abort(404, 'Page '.$page.' is not found.');
        }
        $attachments = $this->page->attachments()->get();
        Debugbar::stopMeasure('db');

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
                'attaches'  => $attachments,
            ]
        );
    }

    /**
     * ソース.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\View\View
     */
    public function source(Request $request, string $page):View
    {
        $entry = $this->page->where('name', $page)->first();

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
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $page
     * @param string                  $file
     *
     * @return Illuminate\View\View
     */
    public function attachments(Request $request, string $page, ?string $file = null)
    {
        Debugbar::startMeasure('db', 'Fetch '.$page.' attached files from db.');
        $attachments = $this->page->getAttachments($page);
        Debugbar::stopMeasure('db');

        if (!empty($file)) {
            // TODO
            return redirect(':api/attachment/'.$attachments->select('attachments.id')->where('attachments.name', $file)->first()->id);
        }

        return view(
           'default.attachment', [
                'page'         => $page,
                'attachments'  => $attachments->select('attachments.*')->get(),
            ]
        );
    }

    /**
     * バックアップ.
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $page
     * @param int                     $age
     *
     * @return Illuminate\View\View
     */
    public function history(Request $request, string $page, ?int $age = null):View
    {
        Debugbar::startMeasure('db', 'Fetch '.$page.' backup data from db.');
        $backups = $this->page->getBackups($page)->select('backups.*')->orderBy('updated_at', 'desc');
        if (!empty($age)) {
            $backups->offset($age - 1)->limit(1);
        }
        Debugbar::stopMeasure('db');

        if (!empty($age)) {
            return view(
               'default.source', [
                    'page'    => $page,
                    'source'  => $backups->first()->source,
                    'title'   => 'Backup of '.$page.'('.$age.')',
                ]
            );
        }

        return view(
           'default.history', [
                'page'         => $page,
                'entries'      => $backups->get(),
            ]
        );
    }

    /**
     * 差分.
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $page
     * @param int                     $age
     *
     * @return Illuminate\View\View
     */
    public function diff(Request $request, string $page, int $age = 1):View
    {
        Debugbar::startMeasure('db', 'Fetch '.$page.' backup data from db.');
        $old = $this->page->getBackups($page)->orderBy('backups.updated_at', 'desc')->offset($age - 1)->value('backups.source');
        $new = $entry = $this->page->select('source')->where('name', $page)->value('source');
        $differ = new Differ();
        Debugbar::stopMeasure('db');

        return view(
           'default.diff', [
                'page'  => $page,
                'diff'  => $differ->diff($old, $new),
            ]
        );
    }

    /**
     * 印刷.
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $page
     *
     * @return Illuminate\View\View
     */
    public function print(Request $request, string $page):View
    {
        $entry = $this->page->where('name', $page)->first();

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
            ]
        );
    }

    /**
     * 編集画面表示.
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $page
     *
     * @return Illuminate\View\View
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
                'hash'        => hash(self::HASH_ALGORITHM, $entry->source),
            ]
         );
    }

    /**
     * 保存処理.
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $page
     * @param string                  $file
     *
     * @return Illuminate\Http\Response
     */
    public function save(Request $request, ?string $page = null):Response
    {
        if (!$request->isMethod('post')) {
            // Method not allowed
            abort(405, 'Method not allowd.');
        }

        if (empty($page)) {
            abort(400, 'Page name is undefined.');
        }

        if ($request->input('action') === 'save') {
            if ($this->page->exists($page)) {
                // ページが存在する場合、DB上のソースを取得。
                $remote = $this->page->where('name', $page)->first();
                $page_id = $remote->id;
                $hash = hash(self::HASH_ALGORITHM, $remote->source);

                if ($hash === hash(self::HASH_ALGORITHM, $request->input('hash'))) {
                    // ハッシュが同じだった場合処理しない
                    return redirect($page);
                } elseif ($hash !== $request->input('hash')) {
                    // 編集前のデータのハッシュ値と比較し、違いがある場合、編集の競合が起きたと判断。
                    // マージ画面を表示する。
                    return view(
                        'default.merge',
                        [
                            'page'   => $page,
                            'origin' => $request->input('origin'),
                            'remote' => $remote->source,
                            'source' => $request->input('source'),
                            'hash'   => hash(self::HASH_ALGORITHM, $request->input('origin')),
                        ]
                    );
                }

                // 更新処理
                $this->page->where('name', $page)->update([
                    'source'     => $request->input('source'),
                    'ip_address' => $request->ip(),
                ]);

                // バックアップ処理
                $backup = Backup::where('page_id', $page_id)->latest()->first();
                //dd(Carbon::now()->timestamp - Carbon::parse($backup->created_at)->timestamp);
                //dd(Config::get('lukiwiki.backup.interval') * 60);
                if ($backup &&
                    !(Carbon::now()->timestamp - Carbon::parse($backup->created_at)->timestamp) <
                    Config::get('lukiwiki.backup.interval')) {
                    // バックアップが存在するが、インターバルの時間未満だった場合、最新のバックアップを上書きする。
                    Backup::where('id', $backup->id)->update([
                        'source'     => $remote->source,
                        'ip_address' => $request->ip(),
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    ]);
                } else {
                    //dd(Backup::where('page_id', $page_id)->latest()->first());
                    // そうでない場合はバックアップを追記
                    Backup::insert([
                        'page_id'    => $page_id,
                        'source'     => $remote->source,
                        'ip_address' => $request->ip(),
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    ]);
                }
            } else {
                // 新規作成
                Page::insert([
                    'name'       => $page,
                    'source'     => $request->input('source'),
                    'ip_address' => $request->ip(),
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
            }

            $request->session()->flash('message', __('Saved'));
        } else {
            $request->session()->flash('message', __('Cancelled'));
        }

        return redirect($page);
    }

    /**
     * アップロード.
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $page
     *
     * @return Illuminate\Http\Response
     */
    public function upload(Request $request, string $page)
    {
        // ファイルのバリデーション
        $this->validate($request, [
            'file.*, file' => [
                // 必須
                'required',
                // バリデーター
                'file',
                // アップロード可能なMIMEタイプを指定
                'mimes:doc,docx,pdf|image|archive',
            ],
        ]);

        $page_id = $this->page->getId($page);

        if (is_array($request->file('file'))) {
            // 複数のファイルを一度にアップロードしたときは配列になるので一つづつ処理
            foreach ($request->file('file') as $entry) {
                self::processUpload($entry, $page_id);
            }
        } else {
            // 一つのファイルのみをアップロードしたときはオブジェクトになる。
            self::processUpload($request->file('file'), $page_id);
        }

        $request->session()->flash('message', __('Uploaded'));

        return redirect($page.':attachments');
    }

    /**
     * アップロード内部処理.
     *
     * @param Illuminate\Http\UploadedFile $entry
     * @param string                       $page
     *
     * @return Illuminate\Http\Response
     */
    private function processUpload(UploadedFile $entry, int $page_id) :bool
    {
        $attach = new Attachment();

        if ($entry->isValid()) {
            $md5Name = hash_file('sha1', $entry->getRealPath());
            $ext = $entry->guessExtension();

            $attach->page_id = $page_id;
            $attach->locked = false;
            $attach->name = $entry->getClientOriginalName();
            $attach->stored_name = basename($entry->storeAs('attachments', $md5Name.'.'.$ext));
            $attach->mime = $entry->getMimeType();
            $attach->size = $entry->getClientSize();
            $attach->save();

            return true;
        }

        return false;
    }

    /**
     * ページ一覧.
     *
     * @return Illuminate\View\View
     */
    public function list():View
    {
        return view(
            'default.list',
            [
                'entries' => $this->page->getEntries(),
            ]
        );
    }

    /**
     * 更新履歴表示.
     *
     * @return Illuminate\View\View
     */
    public function recent():View
    {
        return view(
            'default.recent',
            [
                'entries' => $this->page->getLatest()->get(),
            ]
        );
    }

    /**
     * 検索処理.
     *
     * @param Request $request
     *
     * @return Illuminate\View\View
     */
    public function search(Request $request):View
    {
        $entries = [];
        $keywords = $request->input('keyword');
        if (!empty($keywords)) {
            Debugbar::startMeasure('search', 'Searching...');
            $entries = $this->page->search($keywords)->get()->toArray();
            Debugbar::stopMeasure('parse');
        }
        //dd($entries);

        return view(
            'default.search',
            [
                'keywords' => $keywords,
                'entries'  => $entries,
            ]
        );
    }
}
