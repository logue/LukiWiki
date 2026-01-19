<?php

/**
 * LukiWikiコントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018-2019,2026 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\LukiWiki\Parser;
use App\Models\Attachment;
use App\Models\Backup;
use App\Models\Page;
use Carbon\Carbon;
use Debugbar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use SebastianBergmann\Diff\Differ;

class WikiController extends Controller
{
    // 新旧のデーターの比較に用いる要約用のハッシュのアルゴリズム
    // 使用可能な値：http://php.net/manual/ja/function.hash-algos.php
    // あくまでも新旧のデーターに変化があったかのチェック用途であるため、衝突性能は考慮しない。高速なcrc32で十分だと思う。
    private const HASH_ALGORITHM = 'crc32';

    /** @var \Illuminate\Database\Eloquent\Model ページモデル */
    protected Page $model;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->model = new Page;
    }

    /**
     * ページを読み込む
     */
    public function __invoke(Request $request, ?string $query = null): View
    {
        $page = rawurldecode($query);

        if (empty($page)) {
            // ページが指定されていないときは、デフォルトのページを読み込む
            $page = Config::get('lukiwiki.special_page.default');
        }

        // ページを読み込み
        Debugbar::startMeasure('db', 'Fetch '.$page.' data from db.');
        $entry = $this->model->where('name', '=', $page)->first();
        if (! $entry) {
            // ページが見つからない
            abort(404, __('Page :page is not found.', ['page' => $page]));
        }
        Debugbar::stopMeasure('db');

        // カウンタを更新
        Debugbar::startMeasure('counter', 'Update counter.');
        // TODO:あまり速くない
        $counter = [
            'today' => 0,
            'yesterday' => 0,
            'total' => 0,
            'ip_address' => $request->ip(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
        if ($entry->counter) {
            // カウンタが存在する場合

            // 全カウントを代入
            $counter['total'] = $entry->counter['total'];
            // 最後のカウントからの経過日数
            $interval_day = $entry->counter['updated_at']->day - Carbon::now()->day;

            if ($interval_day === 0 && $entry->counter->ip_address === $counter['ip_address']) {
                // 同一の日のアクセスかつ、同一IPだった場合、カウンタの更新日のみアップデートし、カウント数は更新しない
                $entry->counter->update(['updated_at' => Carbon::now()->toDateTimeString()]);

                // viewでパラメータを$counter変数の値を流用するため既存の値を代入
                $counter['today'] = $entry->counter['today'];
                $counter['yesterday'] = $entry->counter['yesterday'];
            } else {
                // カウンタを加算
                $counter['today']++;
                $counter['total']++;
                // 前日にアクセスが無かった場合、前日のカウントを0にする。
                // それまでの本日のカウントを昨日のカウントに代入して、本日のカウントに1を代入
                $counter['yesterday'] = ($interval_day >= 2) ? 0 : $entry->counter['today'];
                // DBを更新
                $entry->counter->update($counter);
            }
        } else {
            // カウンタが存在しない場合レコード作成
            $counter['page_id'] = $entry->id;
            $counter['today'] = 1;
            $counter['total'] = 1;
            $entry->counter()->insert($counter);
        }
        Debugbar::stopMeasure('counter');

        // WikiをHTMLに変換
        $body = Parser::factory($entry->source, $page);
        $meta = $body->getMeta();
        $content = $body->__toString();

        return view(
            'default.content',
            [
                'page' => $page,
                'content' => $content,
                'entry' => $entry,
                'counter' => $counter,
                'notes' => $meta['note'] ?? null,
            ]
        );
    }

    /**
     * ソース.
     *
     * @param  string  $page  ページ名
     */
    public function source(Request $request, string $page): View
    {
        // DBからページデータを取得
        Debugbar::startMeasure('db', 'Fetch '.$page.' data from db.');
        $entry = $this->model->where('name', '=', $page)->first();
        if (! $entry) {
            abort(404, __('Page :page is not found.', ['page' => $page]));
        }
        Debugbar::stopMeasure('db');

        return view(
            'default.source',
            [
                'page' => $entry->name,
                'source' => $entry->source,
            ]
        );
    }

    /**
     * 添付ファイル一覧.
     *
     * @return mixed
     */
    public function attachments(Request $request, string $page, ?string $file = null)
    {
        $attach_id = null;
        Debugbar::startMeasure('db', 'Fetch '.$page.' data from db.');
        $entry = $this->model->where('name', '=', $page)->first()->attachments();
        if (! $entry) {
            abort(404, __('Page :page is not found.', ['page' => $page]));
        }
        if (! empty($file)) {
            $attach_id = $entry->where('attachments.name', $file)->select('attachments.id')->first()->id;
        }
        Debugbar::stopMeasure('db');

        if (! empty($attach_id)) {
            // TODO: 効率が悪い
            return redirect(':api/attachment/'.$attach_id);
        }

        return view(
            'default.attachment',
            [
                'page' => $page,
                'attachments' => $entry->get(),
            ]
        );
    }

    /**
     * バックアップ.
     */
    public function history(Request $request, string $page, ?int $age = null): View
    {
        Debugbar::startMeasure('db', 'Fetch '.$page.' backup data from db.');
        if (! $this->model->where('name', '=', $page)->exists()) {
            // ページが見つからない
            abort(404, __('Page :page is not found.', ['page' => $page]));
        }
        $backups = $this->model->join('backups', 'backups.page_id', '=', 'pages.id')
            ->where('pages.name', '=', $page)->orderBy('updated_at', 'desc');
        $backup = ! empty($age) ? $backups->offset($age - 1)->first() : $backups->get();

        if (! $backup) {
            abort(404, __('There is no backup for :page.', ['page' => $page]));
        }
        Debugbar::stopMeasure('db');

        if (! empty($age)) {
            return view(
                'default.source',
                [
                    'page' => $page,
                    'age' => $age,
                    'source' => $backup->source,
                ]
            );
        }

        return view(
            'default.history',
            [
                'page' => $page,
                'entries' => $backup,
            ]
        );
    }

    /**
     * 差分.
     *
     * @param  string  $page  ページ名
     * @param  int  $age  世代
     */
    public function diff(Request $request, string $page, int $age = 0): View
    {
        Debugbar::startMeasure('db', 'Fetch '.$page.' backup data from db.');
        $entry = $this->model->where('name', '=', $page);
        if (! $entry) {
            // ページが見つからない
            abort(404, __('Page :page is not found.', ['page' => $page]));
        }
        $new = $entry->value('source');
        $old = $entry->first()->backups()->orderBy('updated_at', 'desc')->offset($age)->value('source');
        Debugbar::stopMeasure('db');

        $differ = new Differ;

        return view(
            'default.diff',
            [
                'page' => $page,
                'offset' => $age,
                'diff' => $differ->diff($old, $new),
            ]
        );
    }

    /**
     * 印刷.
     *
     * @param  string  $page  ページ名
     */
    public function print(Request $request, string $page): View
    {
        Debugbar::startMeasure('db', 'Fetch '.$page.' data from db.');
        $entry = $this->model->where('name', '=', $page)->first();
        if (! $entry) {
            abort(404, __('Page :page is not found.', ['page' => $page]));
        }
        Debugbar::stopMeasure('db');

        // 変換処理
        $body = Parser::factory($entry->source, $page);
        $meta = $body->getMeta();

        return view(
            'layout.print',
            [
                'page' => $entry->name,
                'body' => $body,
                'entry' => $entry,
                'notes' => $meta['note'] ?? null,
            ]
        );
    }

    /**
     * 編集画面表示.
     */
    public function edit(Request $request, ?string $page = null): View
    {
        $p = $page ?? $request->input('page');

        $entry = $this->model->where('name', $p);

        if (! $entry->exists()) {
            // TODO:新規ページ
            return view(
                'default.edit',
                [
                    'page' => $p,
                    'source' => '',
                    'description' => '',
                    'hash' => 0,
                ]
            );
        }

        $ret = $entry->first();

        return view(
            'default.edit',
            [
                'page' => $page,
                'source' => $ret['source'],
                'description' => $ret['description'],
                'hash' => hash(self::HASH_ALGORITHM, (string) $ret['source']),
            ]
        );
    }

    /**
     * 保存処理.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function save(Request $request)
    {
        // ページ名
        $page = $request->post('page');

        if (! $request->isMethod('post')) {
            // Method not allowed
            abort(405, __('Method not allowd.'));
        }

        if (empty($page)) {
            abort(400, __('Page name is undefined.'));
        }

        if ($request->post('action') !== 'save') {
            // キャンセル時の処理
            $request->session()->flash('message', __('Cancelled'));

            return redirect($page);
        }

        // 簡易パスワード確認
        /*
        if (Config::get('lukiwiki.password') !== $request->post('password')) {
            // abort(403, __('Permission denied.'));
            $request->session()->flash('message', __('Permission denied.'));

            return redirect($page);
        }
        */

        if ($this->model->where('name', $page)->exists()) {
            // ページが存在する場合、DB上のソースを取得。
            $remote = $this->model->where('name', $page)->first();

            $page_id = $remote['id'];
            $hash = hash(self::HASH_ALGORITHM, (string) $remote['source']);
            /*
            if ($hash === $request->input('hash')) {
                // 編集前とハッシュが同じだった場合処理しない
                $request->session()->flash('message', __('Hash mismatched. No changes were made.'));

                return redirect($page);
            }
            */
            if ($hash !== $request->input('hash')) {
                // 編集前のデータのハッシュ値と比較し、違いがある場合、編集の競合が起きたと判断。
                // マージ画面を表示する。

                // TODO: 編集した値が空っぽ
                return view(
                    'default.merge',
                    [
                        'page' => $page,
                        'origin' => $request->input('origin'),
                        'remote' => $remote['source'],
                        'source' => $request->input('source'),
                        'hash' => hash(self::HASH_ALGORITHM, $request->input('origin')),
                    ]
                );
            }

            // トランザクション開始
            DB::beginTransaction();

            // 更新処理
            $this->model->where('name', $page)->update([
                'source' => $request->input('source'),
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
                ! (Carbon::now()->timestamp - Carbon::parse($entry->created_at)->timestamp) <
                Config::get('lukiwiki.backup.interval')
                &&
                // 最新のバックアップのIPが同じIPである
                $entry->ip_address === $request->ip()
            ) {
                // バックアップを上書きする
                // この実装のため、インターバル時間内に更新があると、DBの作成日と更新日の値が異なることになる。
                // 同一人物による連続更新かそうでないかはここで判断（※PukiWiki Plus!およびAdv.と同じ仕様）
                // TODO: 更新の競合が多発する状態（更新合戦）の対策
                $backup->update([
                    'source' => $remote->source,
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
            } else {
                if ($backup->count() >= Config::get('lukiwiki.backup.max_entries')) {
                    // 上限件以上あった場合は、一番古いエントリを削除する。
                    $backup->oldest()->first()->delete();
                }

                // バックアップを追記
                Backup::insert([
                    'page_id' => $page_id,
                    'source' => $remote->source,
                    'ip_address' => $request->ip(),
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
            }

            DB::commit();
        } else {
            // 新規作成
            Page::insert([
                'name' => $page,
                'source' => $request->input('source'),
                'ip_address' => $request->ip(),
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);
        }

        // ページ一覧キャッシュを削除
        Page::clearCache();

        $request->session()->flash('message', __('Saved.'));

        return redirect($page);
    }

    /**
     * アップロード.
     *
     * @param  string  $page  ページ名
     */
    public function upload(Request $request, string $page): RedirectResponse
    {
        // 簡易パスワード確認
        if (Config::get('lukiwiki.password') !== $request->post('password')) {
            abort(403, __('Permission denied.'));
        }

        // ファイルのバリデーション
        $request->validate([
            'file.*, file' => [
                // 必須
                'required',
                // バリデーター
                'file',
                // アップロード可能なMIMEタイプを指定
                'mimes:doc,docx,pdf,xslx|image|text|archive|audio|video',
            ],
        ]);

        $page_id = $this->model->getId($page);
        $replace = $request->input('replace') ?? false;

        // 一つづつ処理
        foreach ($request->file('file') as $entry) {
            $this->processUpload($entry, $page_id, $replace);
        }

        $request->session()->flash('message', __('Uploaded'));

        return redirect($page.':attachments');
    }

    /**
     * ページ一覧.
     */
    public function list(): View
    {
        return view(
            'default.list',
            [
                'entries' => $this->model->getEntries(),
            ]
        );
    }

    /**
     * 更新履歴表示.
     */
    public function recent(): View
    {
        return view(
            'default.recent',
            [
                'entries' => $this->model->getLatest()->get(),
            ]
        );
    }

    /**
     * 検索処理.
     */
    public function search(Request $request): View
    {
        $entries = [];
        $keywords = $request->input('keyword');
        if (! empty($keywords)) {
            Debugbar::startMeasure('search', 'Searching...');
            $entries = $this->model->search($keywords)->get()->toArray();
            Debugbar::stopMeasure('parse');
        }
        // dd($entries);

        return view(
            'default.search',
            [
                'keywords' => $keywords,
                'entries' => $entries,
            ]
        );
    }

    /**
     * アップロード内部処理.
     */
    private function processUpload(UploadedFile $entry, int $page_id, bool $replace = false): bool
    {
        $attach = $this->model->find($page_id)->attachment();

        if (! $entry->isValid()) {
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

            if ($old_attach && ! Attachment::where('stored_name', $old_attach->stored_name)->exists()) {
                // 同一ハッシュのファイルがDBに登録されていない（他から参照されていない）場合、ファイルを削除する
                Storage::delete('attachments/'.$old_attach->stored_name);
            }

            // ファイル差し替え
            Attachment::updateOrCreate([
                'page_id' => $page_id,
                'name' => $entry->getClientOriginalName(),
            ], [
                'stored_name' => basename($entry->storeAs('attachments', $stored_name)),
                'mime' => $entry->getMimeType(),
                'size' => $entry->getClientSize(),
            ]);
        } else {
            // 上書き（追記）
            Attachment::insert([
                'page_id' => $page_id,
                'name' => $entry->getClientOriginalName(),
                'stored_name' => basename($entry->storeAs('attachments', $stored_name)),
                'mime' => $entry->getMimeType(),
                'size' => $entry->getClientSize(),
            ]);
        }

        // トランザクション終了
        DB::commit();

        return true;
    }
}
