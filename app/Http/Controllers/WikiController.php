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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use SebastianBergmann\Diff\Differ;

class WikiController extends Controller
{
    // 新旧のデーターの比較に用いる要約用のハッシュのアルゴリズム
    // 使用可能な値：http://php.net/manual/ja/function.hash-algos.php
    // あくまでも新旧のデーターに変化があったかのチェック用途であるため、衝突性能は考慮しない。高速なcrc32で十分だと思う。
    const HASH_ALGORITHM = 'crc32';

    public function __construct()
    {
        // ページモデルオブジェクト
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
            // ページが指定されていないときは、デフォルトのページを読み込む
            $page = Config::get('lukiwiki.special_page.default');
        }

        Debugbar::startMeasure('db', 'Fetch '.$page.' data from db.');
        $entry = $this->page->where('name', $page)->first();

        if (!$entry) {
            return abort(404, 'Page '.$page.' is not found.');
        }
        $this->page->countUp($page);
        Debugbar::stopMeasure('db');

        Debugbar::startMeasure('parse', 'Converting wiki data...');
        //dd($entry->source);
        $lines = explode("\n", str_replace([chr(0x0d).chr(0x0a), chr(0x0d), chr(0x0a)], "\n", $entry->source));

        $body = new RootElement($page, 0, ['id' => 0]);
        $body->parse($lines);

        $meta = $body->getMeta();
        $content = $body->__toString();
        Debugbar::stopMeasure('parse');

        return view(
           'default.content',
           [
                'page'      => $page,
                'content'   => $content,
                'entry'     => $entry,
                'notes'     => $meta['note'] ?? null,
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
        // DBからページデータを取得
        Debugbar::startMeasure('db', 'Fetch '.$page.' data from db.');
        $entry = $this->page->where('name', $page)->first();
        if (!$entry) {
            return abort(404, 'Page '.$page.' is not found.');
        }
        Debugbar::stopMeasure('db');

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
        $attach_id = null;
        Debugbar::startMeasure('db', 'Fetch '.$page.' attached files from db.');
        $attachments = $this->page->getAttachments($page);
        if (!empty($file)) {
            $attach_id = $attachments->where('attachments.name', $file)->select('attachments.id')->first()->id;
        }
        Debugbar::stopMeasure('db');

        if (!empty($attach_id)) {
            // TODO: 効率が悪い
            return redirect(':api/attachment/'.$attach_id);
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
            $backup = $backups->offset($age - 1)->limit(1)->first();
        } else {
            $backup = $backups->get();
        }
        Debugbar::stopMeasure('db');

        if (!empty($age)) {
            return view(
               'default.source', [
                    'page'    => $page,
                    'source'  => $backup->source,
                    'title'   => 'Backup of '.$page.'('.$age.')',
                ]
            );
        }

        return view(
           'default.history', [
                'page'         => $page,
                'entries'      => $backup,
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
        $body->parse($lines);

        $meta = $body->getMeta();
        $content = $body->__toString();
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
        $p = $page ?? $request->input('page') ?? Config::get('lukiwiki.special_page.default');

        $entry = $this->page->where('name', $p)->first();

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
     * @return Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, ?string $page = null):RedirectResponse
    {
        if (!$request->isMethod('post')) {
            // Method not allowed
            abort(405, 'Method not allowd.');
        }

        if (empty($page)) {
            abort(400, 'Page name is undefined.');
        }

        if ($request->input('action') !== 'save') {
            // キャンセル時の処理
            $request->session()->flash('message', __('Cancelled'));

            return redirect($page);
        }

        if ($this->page->exists($page)) {
            // ページが存在する場合、DB上のソースを取得。
            $remote = $this->page->where('name', $page)->first();
            $page_id = $remote->id;
            $hash = hash(self::HASH_ALGORITHM, $remote->source);

            if ($hash === hash(self::HASH_ALGORITHM, $request->input('hash'))) {
                // 編集前とハッシュが同じだった場合処理しない
                return redirect($page);
            } elseif ($hash !== $request->input('hash')) {
                // 編集前のデータのハッシュ値と比較し、違いがある場合、編集の競合が起きたと判断。
                // マージ画面を表示する。

                // TODO: 編集した値が空っぽ
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

            // トランザクション開始
            \DB::beginTransaction();

            // 更新処理
            $this->page->where('name', $page)->update([
                'source'     => $request->input('source'),
                'ip_address' => $request->ip(),
            ]);

            // バックアップ処理
            $backup = Backup::where('page_id', $page_id);

            $entry = $backup->latest()->first();
            if (
                // バックアップが存在する
                $entry
                &&
                // 過去のバックアップがインターバルの時間未満である
                !(Carbon::now()->timestamp - Carbon::parse($entry->created_at)->timestamp) <
                Config::get('lukiwiki.backup.interval')
                &&
                // 最新のバックアップのIPが同じIPである
                $entry->ip_address === $request->ip()
            ) {
                // バックアップを上書きする
                // この実装のため、インターバル時間未満で更新があると、DBの作成日と更新日の値が異なることになる。
                // PukiWiki Plus!およびAdv.と同じ仕様
                $backup->update([
                    'source'     => $remote->source,
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
            } else {
                if ($backup->count() >= Config::get('lukiwiki.backup.entries')) {
                    // 上限件以上あった場合は、一番古いエントリを削除する。
                    $backup->oldest()->first()->delete();
                }

                // バックアップを追記
                Backup::insert([
                    'page_id'    => $page_id,
                    'source'     => $remote->source,
                    'ip_address' => $request->ip(),
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
            }

            \DB::commit();
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

        return redirect($page);
    }

    /**
     * アップロード.
     *
     * @param Illuminate\Http\Request $request
     * @param string                  $page
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request, string $page):RedirectResponse
    {
        // ファイルのバリデーション
        $this->validate($request, [
            'file.*, file' => [
                // 必須
                'required',
                // バリデーター
                'file',
                // アップロード可能なMIMEタイプを指定
                'mimes:doc,docx,pdf|image|text|archive|audio|video',
            ],
        ]);

        $page_id = $this->page->getId($page);
        $replace = $request->input('replace') ?? false;

        if (is_array($request->file('file'))) {
            // 複数のファイルを一度にアップロードしたときは配列になるので一つづつ処理
            foreach ($request->file('file') as $entry) {
                self::processUpload($entry, $page_id, $replace);
            }
        } else {
            // 一つのファイルのみをアップロードしたときはオブジェクトになる。
            self::processUpload($request->file('file'), $page_id, $replace);
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
     * @return bool
     */
    private function processUpload(UploadedFile $entry, int $page_id, bool $replace = false) :bool
    {
        $attach = $this->page->find($page_id)->attachment();

        if (!$entry->isValid()) {
            return false;
        }

        // トランザクション開始
        DB::beginTransaction();

        // 保存するファイル名
        $md5Name = hash_file('sha1', $entry->getRealPath());
        $ext = $entry->guessExtension();
        $stored_name = $md5Name.'.'.$ext;

        if ($replace) {
            // 同名で登録されている添付ファイルがあるか
            $old_attach = $attach->where('name', $entry->getClientOriginalName())->first();

            if ($old_attach) {
                if (!Attachement::where('stored_name', $old_attach->stored_name)->exists()) {
                    // 同一ハッシュのファイルがDBに登録されていない（他から参照されていない）場合、ファイルを削除する
                    Storage::delete('attachments/'.$old_attach->stored_name);
                }
            }

            // ファイル差し替え
            Attachment::updateOrCreate([
                'page_id'     => $page_id,
                'name'        => $entry->getClientOriginalName(),
            ], [
                'count'       => $count,
                'locked'      => $locked,
                'stored_name' => basename($entry->storeAs('attachments', $stored_name)),
                'mime'        => $entry->getMimeType(),
                'size'        => $entry->getClientSize(),
            ]);
        } else {
            // 上書き（追記）
            Attachment::insert([
                'page_id'     => $page_id,
                'name'        => $entry->getClientOriginalName(),
                'count'       => $count,
                'locked'      => $locked,
                'stored_name' => basename($entry->storeAs('attachments', $stored_name)),
                'mime'        => $entry->getMimeType(),
                'size'        => $entry->getClientSize(),
            ]);
        }

        // トランザクション終了
        DB::commit();

        return true;
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
