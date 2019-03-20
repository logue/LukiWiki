<?php
/**
 * PukiWiki書式をLukiWiki書式に変換してDBに保存するジョブ.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportPukiWikiData implements ShouldQueue
{
    /**
     * 最大試行回数.
     *
     * @var int
     */
    public $tries = 1;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $files = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $path)
    {
        $this->files = Storage::files($path.'/wiki/');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Start Wiki data convertion.');

        foreach ($this->files as &$file) {
            ProcessWikiData::dispatch($file);
        }
        Log::info('Finish.');

        Log::info('Clear cache');
        \Cache::flush();
        Log::info('Finish.');
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
        Log::error('Import Wiki data Job has been failed.');
        Log::error($exception);
    }
}
