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
use Image;

class ImportPukiWikiAttach implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $files = [];

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
            $meta = [];
            $count = 0;
            $locked = false;
            // 添付ファイルの名前を取得（かなりいい加減な正規表現だが・・・）
            // [ページ名]_[ファイル名].[バックアップ世代]という形式。
            // 添付するファイル名に制約がかかるため、LukiWikiではDBで管理する。
            if (!preg_match('/(\w+)_(\w+)(?:\.(\d+|log))?$/', pathinfo($file, PATHINFO_BASENAME), $matches)) {
                return;
            }

            // ログやバックアップファイルは無視
            if (!empty($matches[3])) {
                continue;
            }

            // ページ名を取得
            $page = hex2bin($matches[1]);

            //if ($page !== 'FF11裏話/第５章　ギルド追放～情報操作') continue;

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
                Log::info('-> File: '.$original_name.' is already registed to db. Skipped.');

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
            Log::info('-> File: '.$original_name.' Copied to '.$stored_name.'.');
            Storage::copy($file, $dest);

            // Storageクラスにハッシュなどの命令がないためファイルの実体のパスを取得
            $from = str_replace('\\', DIRECTORY_SEPARATOR, storage_path('app/'.$file));

            // ファイルのメタ情報を取得
            $info = \MediaInfo::extract($from);
            $mime = $info['mime_type'] ?? Storage::mimeType($dest);

            if (empty($info['error'])) {
                Log::info('-> Extract meta data.');
                // 画像の大きさを取得
                if (isset($info['video'])) {
                    Log::info('-> Fetch image size.');
                    $meta['width'] = $info['video']['resolution_x'];
                    $meta['height'] = $info['video']['resolution_y'];
                }
                // 演奏時間を取得
                if (isset($info['playtime_string'])) {
                    Log::info('-> Fetch play time.');
                    $meta['playtime'] = $info['playtime_string'] ?? null;
                }

                if (isset($info['comments']['picture'])) {
                    // アルバムアートがある場合サムネイルディレクトリに保存
                    Log::info('-> Extract album art.');
                    $picture = $meta['comments']['picture'][0];
                    switch ($picture['image_mime']) {
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
                    $meta['thumbnail'] = $thumb_name;
                }

                if (strpos($mime, 'image') !== false) {
                    // 画像の場合サムネイル作成
                    Log::info('-> Process image file.');
                    $image = Image::make(file_get_contents($from));
                    $meta['width'] = $image->width();
                    $meta['height'] = $image->height();
                    Log::info('-> Generate thumbnail.');
                    $image->resize(256, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    // え、jpeg固定！？
                    //$image->save(str_replace('\\', DIRECTORY_SEPARATOR, storage_path('app/thumbnails/'.$s.'.jpg')));
                    Storage::put('thumbnails/'.$s.'.jpg', $image->encode('jpg'));
                    $meta['thumbnail'] = $s.'.jpg';
                }
                $size = $info['filesize'];
            } else {
                $size = Storage::size($file);
            }

            Attachment::updateOrCreate([
                'page_id'     => $page_id,
                'name'        => $original_name,
            ], [
                'count'       => $count,
                'user_id'     => 0,
                'ip'          => $_SERVER['REMOTE_ADDR'],
                'locked'      => $locked,
                'stored_name' => $stored_name,
                'mime'        => $mime,
                'hash'        => hash_file('md5', $from),
                'size'        => $size,
                'meta'        => $meta,
                'created_at'  => Carbon::createFromTimestamp(filectime($from))->format('Y-m-d H:i:s'),
                'updated_at'  => Carbon::createFromTimestamp(Storage::lastModified($file))->format('Y-m-d H:i:s'),
            ]);
        }
        Log::info('Finish.');
    }
}
