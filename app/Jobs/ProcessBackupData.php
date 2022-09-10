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
    public int $tries = 1;

    private string $file;

    private string $page;

    /**
     * Create a new job instance.
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        $this->page = hex2bin(pathinfo($this->file, PATHINFO_FILENAME));
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Loading "'.$this->file.'"...');

        if (empty($this->page)) {
            // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
            return;
        }

        // :が含まれるページ名は_に変更
        $pagename = str_replace(':', '_', $this->page);
        // ページが存在しない場合、移行はしない。（IDで管理するため）
        $page_id = Page::where('name', $pagename)->pluck('id')->first();
        if (! $page_id) {
            return;
        }
        Log::info('Page: '.$pagename);

        // Storageクラスに作成日を取得する関数がないためファイルの実体のパスを取得
        $from = str_replace('\\', DIRECTORY_SEPARATOR, storage_path('app/'.$this->file));

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
                $data = \lzf_decompress(Storage::get($this->file));
                break;
            case 'gz':
                $handle = \gzopen($from, 'r');
                while (! gzeof($handle)) {
                    $data .= \gzread($handle, 1024);
                }
                gzclose($handle);
                break;
            case 'bz2':
                // PukiWiki Adv.
                $handle = \bzopen($from, 'r');
                while (! feof($handle)) {
                    $data .= \bzread($handle, 1024);
                }
                bzclose($handle);
                break;
            default:
                return;
        }

        if (empty($data)) {
            // バックアップデーターが存在しない（中身が空白）
            return;
        }

        $entries = [];
        $age = 0;

        foreach (explode("\n", $data) as $line) {
            // バックアップデーターをパース
            if (preg_match('/^(?:\>{10}\s(\d+)\s?(\d+)?)$/', $line, $matchs)) {
                $age++;

                // 実際ページを保存した時間が指定されている場合（タイムスタンプを更新しないをチェックして更新した場合）
                // そちらのパラメータをバックアップの日時として使用する。（Plus!およびAdv.独自仕様）

                // 割当
                $entries[$age] = [
                    'page_id' => $page_id,
                    'created_at' => (int) $matchs[1],
                    'updated_at' => isset($matchs[2]) ? (int) $matchs[2] : (int) $matchs[1],
                ];

            //dd($match);
            } else {
                // 中身
                $entries[$age]['data'][] = rtrim($line);
            }
        }

        foreach ($entries as $age => $entry) {
            if (empty($entry['data'])) {
                continue;
            }
            Backup::updateOrCreate([
                'page_id' => $entry['page_id'],
                'created_at' => Carbon::createFromTimestamp($entry['created_at'])->format('Y-m-d H:i:s'),
            ], [
                'source' => implode("\n", $entry['data']),
                'updated_at' => Carbon::createFromTimestamp($entry['updated_at'])->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * 失敗したジョブの処理.
     *
     * @param  \Throwable  $exception
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Import Backup data Job has been failed: '.$this->page);
        Log::error($exception->__toString());
    }
}
