<?php
/**
 * DB最適化.
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

class OptimizeDB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $db;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = \Config::get('database.default');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Start Optimize');
        if ($this->db === 'sqlite') {
            \DB::statement('VACUUM');
        } elseif ($this->db === 'mysql') {
            \DB::statement('OPTIMIZE TABLE '.\DB::getTablePrefix().'.*');
        } elseif ($this->db === 'pgsql') {
            \DB::statement('VACUUM FULL');
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
        Log::error('Optimize Job has been failed.');
        Log::error($exception);
    }
}