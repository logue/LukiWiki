<?php
/**
 * 添付ファイル取り込みのメイン処理.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Jobs;

use App\Models\Attachment;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessAttachmentData implements ShouldQueue
{
    /**
     * 最大試行回数.
     *
     * @var int
     */
    public $tries = 1;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $file;
    private $attach_dir;
    private $page;
    private $original_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        if (!preg_match('/(\w+)_(\w+)(?:\.(\d+|log))?$/', pathinfo($file, PATHINFO_BASENAME), $matches)) {
            return;
        }

        // ログやバックアップファイルは無視
        if (!empty($matches[3])) {
            return;
        }
        // 添付ファイルディレクトリ
        $this->attach_dir = \Config::get('lukiwiki.directory.attach');
        // ページ名
        $this->page = hex2bin($matches[1]);
        // 元のファイル名を取得
        $this->original_name = hex2bin($matches[2]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $meta = [];
        $count = 0;
        $locked = false;
        // 添付ファイルの名前を取得（かなりいい加減な正規表現だが・・・）
        // [ページ名]_[ファイル名].[バックアップ世代]という形式。
        // 添付するファイル名に制約がかかるため、LukiWikiではDBで管理する。

        // :が含まれるページ名は_に変更
        $page = preg_replace('/\:/', '_', $this->page);
        // ページが存在しない場合、移行はしない。（IDで管理するため）
        $page_id = Page::where('name', $page)->pluck('id')->first();
        if (!$page_id) {
            return;
        }
        Log::info('Page: '.$page);

        if (Attachment::where(['page_id'=>$page_id, 'name' => $this->original_name])->exists()) {
            // すでにデーターベースに登録されている場合スキップ
            Log::info('-> File: '.$this->original_name.' is already registed to db. Skipped.');

            return;
        }

        // 添付ファイルのバックアップは移行しない
        //if (!empty($matches[3]) {
        //    if ($matches[3] === 'log'){
        //        $count = (int) file_get_contents($file);
        //    }else{
        //        $backup_no = (int) $matches[3];
        //    }
        //}

        // 拡張子を取得
        $ext = substr($this->original_name, strrpos($this->original_name, '.') + 1);

        // 閲覧回数を取得
        $count = 0;
        if (Storage::exists($this->file.'.log')) {
            $log = explode("\n", trim(Storage::get($this->file.'.log')));
            $count = (int) explode(',', array_unshift($log))[0];
            $locked = $count !== 1 && array_shift($log) === '1';
        }

        // Storageクラスにハッシュなどの命令がないためファイルの実体のパスを取得
        $from = str_replace('\\', DIRECTORY_SEPARATOR, storage_path('app/'.$this->file));

        // サーバーに保存する実際のファイル名はハッシュ値＋拡張子
        $s = hash_file('sha1', $from);
        $stored_name = $s.'.'.$ext;
        // 保存先のパス
        $dest = $this->attach_dir.'/'.$stored_name;

        if (!Storage::exists($dest)) {
            // LukiWikiの添付ディレクトリにコピー
            Log::info('-> File: '.$this->original_name.' Copied to '.$stored_name.'.');
            Storage::copy($this->file, $dest);
        } else {
            // TODO:同一のファイルが別ページにアップされている
            Log::info('-> File: '.$this->original_name.' is already exists or same file('.$stored_name.'). Skipped.');
        }

        Attachment::updateOrCreate([
            'page_id'     => $page_id,
            'name'        => $this->original_name,
        ], [
            'count'       => $count,
            'locked'      => $locked,
            'stored_name' => $stored_name,
            'mime'        => Storage::mimeType($dest),
            'size'        => Storage::size($this->file),
            'created_at'  => Carbon::createFromTimestamp(filectime($from))->format('Y-m-d H:i:s'),
            'updated_at'  => Carbon::createFromTimestamp(Storage::lastModified($this->file))->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 失敗したジョブの処理.
     *
     * @param Exception $exception
     *
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error('Convert Error: '.$this->original_name.' ('.$this->page.')');
        Log::error($exception);
    }
}
