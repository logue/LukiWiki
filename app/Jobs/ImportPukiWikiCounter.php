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

class ImportPukiWikiCounter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * 最大試行回数.
     *
     * @var int
     */
    public $tries = 1;

    private $files = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $path)
    {
        $this->files = Storage::files($path.'/counter/');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Start Counter data convertion.');

        foreach ($this->files as &$file) {
            ProcessCounterData::dispatch($file);
        }
        Log::info('Finish.');
    }
}
