<?php
/**
 *バックアップをパースしてDBに保存するジョブ.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Jobs;

use App\Models\Backup;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessBackupData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * 最大試行回数.
     *
     * @var int
     */
    public $tries = 1;

    private $file;
    private $page;

    /**
     * Create a new job instance.
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        $this->page = hex2bin(pathinfo($this->file, PATHINFO_FILENAME));

        if (empty($this->page)) {
            // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
            return;
        }
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Loading "'.$this->file.'"...');

        // :が含まれるページ名は_に変更
        $page = preg_replace('/\:/', '_', $this->page);
        // ページが存在しない場合、移行はしない。（IDで管理するため）
        $page_id = Page::where('name', $page)->pluck('id')->first();
        if (!$page_id) {
            return;
        }
        Log::info('Page: '.$page);

        // :が含まれるページ名は_に変更する。
        $page = preg_replace('/\:/', '_', $this->page);

        // Storageクラスに作成日を取得する関数がないためファイルの実体のパスを取得
        $from = str_replace('\\', \DIRECTORY_SEPARATOR, storage_path('app/'.$this->file));

        // 拡張子を取得
        $ext = substr($this->file, strrpos($this->file, '.') + 1);

        // バックアップを読み込む
        $data = null;
        switch ($ext) {
            case 'txt':
                $data = Storage::get($this->file);
                break;
            case 'lzf':
                // PukiWiki Adv.
                $data = lzf_decompress(Storage::get($this->file));
                break;
            case 'gz':
                $handle = gzopen($from, 'r');
                while (!gzeof($handle)) {
                    $data .= gzread($handle, 1024);
                }
                gzclose($handle);
                break;
            case 'bz2':
                // PukiWiki Adv.
                $handle = bzopen($from, 'r');
                while (!feof($handle)) {
                    $data .= bzread($handle, 1024);
                }
                bzclose($handle);
                break;
        }

        if (empty($data)) {
            // バックアップデーターが存在しない（中身が空白）
            return;
        }

        $entries = [];
        $age = 0;

        foreach (explode("\n", $data) as $line) {
            // バックアップデーターをパース
            if (preg_match('/^\>\>\>\>\>\>\>\>\>\>\s(\d+)(?:\s(\d+))?$/', $line, $match)) {
                $age++;

                // 実際ページを保存した時間が指定されている場合（タイムスタンプを更新しないをチェックして更新した場合）
                // そちらのパラメータをバックアップの日時として使用する。（Plus!およびAdv.独自仕様）

                // 割当
                $entries[$age] = [
                    'page_id'   => $page_id,
                    'created_at'=> (int) $match[1],
                    'updated_at'=> isset($match[2]) ? (int) $match[2] : (int) $match[1],
                ];

            //dd($match);
            } else {
                // 中身
                $entries[$age]['data'][] = rtrim($line);
            }
        }

        foreach ($entries as $age=>$entry) {
            if (empty($entry['data'])) {
                continue;
            }
            Backup::updateOrCreate([
                'page_id'     => $entry['page_id'],
                'created_at'  => Carbon::createFromTimestamp($entry['created_at'])->format('Y-m-d H:i:s'),
            ], [
                'source'      => implode("\n", $entry['data']),
                'updated_at'  => Carbon::createFromTimestamp($entry['updated_at'])->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * 失敗したジョブの処理.
     *
     * @param \Exception $exception
     */
    public function failed(\Exception $exception)
    {
        Log::error('Import Backup data Job has been failed.');
        Log::error($exception);
    }
}
