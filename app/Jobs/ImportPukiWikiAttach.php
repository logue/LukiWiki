<?php
/**
 * PukiWikiの添付ファイルをLukiWikiの添付ファイルに変換してDBに保存するジョブ.
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
use Illuminate\Support\Str;

class ImportPukiWikiAttach implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $files = [];
    private static $ignore_keys = ['GETID3_VERSION', 'filesize', 'filepath', 'filename', 'filenamepath', 'fileformat', 'gif', 'png', 'tags', 'mpeg', 'midi', 'id3v2', 'id3v1', 'tags_html', 'comments_html'];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $path)
    {
        $this->files = Storage::files($path.'/attach/');
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
            $count = 0;
            $locked = false;
            // 添付ファイルの名前を取得（かなりいい加減な正規表現だが・・・）
            // [ページ名]_[ファイル名].[バックアップ世代]という形式。
            // 添付するファイル名に制約がかかるため、LukiWikiではDBで管理する。
            if (!preg_match('/(\w+)_(\w+)(?:\.(\d+|log))?$/', pathinfo($file, PATHINFO_BASENAME), $matches)) {
                return;
            }

            // ログやバックアップファイルは無視
            if (isset($matches[3])) {
                continue;
            }

            // ページ名を取得
            $page = hex2bin($matches[1]);

            // :が含まれるページ名は_に変更
            $page = preg_replace('/\:/', '_', $page);
            // ページが存在しない場合、移行はしない。（IDで管理するため）
            $page_id = Page::where('name', $page)->pluck('id')->first();
            if (!$page_id) {
                continue;
            }
            Log::info('Page: '.$page);

            // 元のファイル名を取得
            $original_name = hex2bin($matches[2]);

            if (Attachment::where(['page_id'=>$page_id, 'name' => $original_name])->exists()) {
                // すでにデーターベースに登録されている場合スキップ
                Log::info('->File: '.$original_name.' Skipped.');

                continue;
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
            $ext = substr($original_name, strrpos($original_name, '.') + 1);

            // 閲覧回数を取得
            $count = 0;
            if (Storage::exists($file.'.log')) {
                $log = explode("\n", trim(Storage::get($file.'.log')));
                $count = (int) explode(',', array_unshift($log))[0];
                $locked = $count !== 1 && array_shift($log) === '1';
            }

            // サーバーに保存する実際のファイル名は40文字のランダムの文字列＋拡張子
            $s = Str::random(40);
            $stored_name = $s.'.'.$ext;
            // 保存先のパス
            $dest = \Config::get('lukiwiki.directory.attach').'/'.$stored_name;

            // LukiWikiの添付ディレクトリにコピー
            Storage::copy($file, $dest);
            Log::info('->File: '.$original_name.' Copied to '.$stored_name.'.');

            // Storageクラスにハッシュなどの命令がないためファイルの実体のパスを取得
            $from = str_replace('\\', DIRECTORY_SEPARATOR, storage_path('app/'.$file));

            // ファイルのメタ情報を取得
            $info = \MediaInfo::extract($from);
            // 無視するキーを除外
            $meta = array_diff_key($info, array_flip(static::$ignore_keys));

            if (isset($meta['comments']['picture'])){
                $picture = $meta['comments']['picture'][0];
                switch ($picture['image_mime']){
                    case 'image/jpeg':
                        $thumb_name = $s.'.jpg';
                        break;
                    case 'image/png':
                        $thumb_name = $s.'.png';
                        break;
                    case 'image/gif':
                        $thumb_name = $s.'.gif';
                        break;

                }
                Storage::put('thumbnails/'.$thumb_name, $picture['data']);
            }
            unset($meta['comments']);

            try {
                Attachment::updateOrCreate([
                    'page_id'     => $page_id,
                    'name'        => $original_name,
                ], [
                    'count'       => $count,
                    'user_id'     => 0,
                    'ip'          => $_SERVER['REMOTE_ADDR'],
                    'locked'      => $locked,
                    'stored_name' => $stored_name,
                    'mime'        => $info['mime_type'] ?? Storage::mimeType($dest),
                    'hash'        => hash_file('md5', $from),
                    'size'        => Storage::size($file),
                    'meta'        => $meta,
                    'created_at'  => Carbon::createFromTimestamp(filectime($from))->format('Y-m-d H:i:s'),
                    'updated_at'  => Carbon::createFromTimestamp(Storage::lastModified($file))->format('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {
                dd($meta);
            }
        }
        Log::info('Finish.');
    }
}
