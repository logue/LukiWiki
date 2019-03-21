<?php
/**
 * PukiWikiカウンター取り込み処理.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Jobs;

use App\Models\Counter;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessCounterData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     *
     * @return void
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
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Loading "'.$this->file.'"...');

        // :が含まれるページ名は_に変更
        $page = preg_replace('/\:/', '_', $this->page);
        // ページが存在しない場合、移行はしない。（IDで管理するため）
        $page_id = Page::where('name', $page)->pluck('id')->first();
        if (!$page_id) {
            Log::info('Skipped');

            return;
        }
        Log::info('Page: '.$page);

        $data = explode("\n", Storage::get($this->file));

        Counter::updateOrCreate(
            [
                // 更新対象
                'page_id'        => $page_id,
            ], [
                'total'           => $data[0],
                'today'           => $data[2],
                'yesterday'       => $data[3],
                'ip_address'      => $data[4],
                'updated_at'      => Carbon::parse($data[1])->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * 失敗したジョブの処理.
     *
     * @param \Exception $exception
     *
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error('Convert Error: '.$this->page);
        Log::error($exception);
    }
}
