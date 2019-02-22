<?php
/**
 * PukiWikiの添付ファイルをLukiWikiの添付ファイルに変換してDBに保存するジョブ.
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

class ImportPukiWikiAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $files = [];
    private $attach_dir;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $path)
    {
        $this->files = Storage::files($path.'/attach/');
        $this->attach_dir = \Config::get('lukiwiki.directory.attach');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Start Attached file convertion.');

        foreach ($this->files as &$file) {
            ProcessAttachmentData::dispatch($file);
        }
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
        Log::error('Import Attach data Job has been failed.');
        Log::error($exception);
    }
}
