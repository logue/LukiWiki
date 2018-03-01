<?php
/**
 * Wikiのファイルシステム関数.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Utility;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class WikiFileSystem
{
    // キャッシュ名のプレフィックス
    const DIRECTORY_CACHE_PREFIX = 'directory-';
    // キャッシュの有効期間
    const DIRECTORY_CACHE_EXPIRE = 1000;

    /** ディレクトリのパス */
    private $directory;
    /** ディレクトリの更新日（ファイル一覧キャッシュなどで使用） */
    private $modified;
    /** 格納ファイルの拡張子 */
    private $ext = '.txt';
    /** キャッシュファイル名 */
    private $cache_name;

    /**
     * コンストラクタ
     */
    public function __construct($type, $ext = '.txt')
    {
        $directory = Config::get('lukiwiki.directory');
        if (empty($directory[$type])) {
            // 設定がない
            throw new \Exception('Not defineded.');
        }
        if (!Storage::exists($directory[$type])) {
            // ディレクトリがない
            Storage::makeDirectory($directory[$type]);
        }
        if (Storage::getMetadata($directory[$type])['type'] !== 'dir') {
            // ディレクトリでない
            throw new \Exception('Not a directory.');
        }
        //if (!Storage::isWritable($directory[$type])) {
        // 書き込めない
        //   throw new \Exception('Directory is not writable.');
        //}
        $this->directory = $directory[$type];
        $this->modified = Storage::lastModified($directory[$type]);
        $this->cache_name = self::DIRECTORY_CACHE_PREFIX.$type;
    }

    /**
     * ページを保存.
     *
     * @param string $page    ページ名
     * @param string $content 内容
     */
    public function __set($page, $content)
    {
        Cache::forget($this->cache_name);

        Storage::put($this->directory.'/'.self::encode($page).$this->ext, $contents);
    }

    /**
     * ページを取得.
     *
     * @param string $page ページ名
     *
     * @return string
     */
    public function __get($page)
    {
        return Storage::get($this->directory.'/'.self::encode($page).$this->ext);
    }

    /**
     * ページの存在確認.
     *
     * @param string $page ページ名
     *
     * @return bool
     */
    public function __isset($page)
    {
        return Storage::exists($this->directory.'/'.self::encode($page).$this->ext);
    }

    /**
     * ページの削除.
     *
     * @param string $page ページ名
     */
    public function __unset($page)
    {
        Cache::forget($this->cache_name);

        return Storage::delete($this->directory.'/'.self::encode($page).$this->ext);
    }

    /**
     * ページのリネーム.
     *
     * @param string $from 変更前
     * @param string $to   変更後
     */
    public function rename($from, $to)
    {
        Cache::forget($this->cache_name);
        Storage::move($this->directory.'/'.self::encode($from).$this->ext, $this->directory.'/'.self::encode($to).$this->ext);
    }

    /**
     * ページの最終更新日.
     *
     * @param string $page
     */
    public function modified($page)
    {
        return Storage::lastModified($this->directory.'/'.self::encode($page).$this->ext);
    }

    /**
     * ページのハッシュ.
     *
     * @param string $page
     */
    public function hash($page)
    {
        //return Storage::hash($this->directory.'/'.self::encode($page).$this->ext);
    }

    /**
     * タイムスタンプを更新.
     */
    public function touch($page, $time)
    {
        Cache::forget($this->cache_name);

        return touch(storage_path($this->directory.'/'.self::encode($page).$this->ext), $time);
    }

    /**
     * ファイル名とページ名の対応リストを取得する.
     *
     * @param string $type ファイルの種類
     *
     * @return array
     */
    public function __invoke()
    {
        return Cache::remember($this->cache_name, self::DIRECTORY_CACHE_EXPIRE, function () {
            // ファイル名一覧の処理はキャッシュに入れる
            $ret = [];
            foreach (Storage::files($this->directory) as $filepath) {
                $page = self::decode($filepath);

                if (empty($page)) {
                    // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
                    continue;
                }

                $ret[$page] = ['path' => $filepath, 'modified' => $this->modified($page), 'hash' => $this->hash($page)];
            }

            return $ret;
        });
    }

    /**
     * パスからファイル名を取得.
     *
     * @param string $path
     *
     * @return string
     */
    private static function getFileName($path)
    {
        // パスを削除
        return substr($path, strrpos($path, '/') + 1);
    }

    /**
     * ページ名をWikiのファイル名に変更.
     *
     * @param string $page ページ名
     *
     * @return string
     */
    public static function encode($page)
    {
        return strtoupper(bin2hex($page));
    }

    /**
     * ファイル名をWikiのページ名に変更.
     *
     * @param string $filepath ファイル名
     *
     * @return string
     */
    public static function decode($filepath)
    {
        // ファイル名を取得
        $file = self::getFileName($filepath);
        // 先頭の/と拡張子を削除
        $filename = substr($file, 0, strrpos($file, '.'));

        if (empty($filename)) {
            return;
        }

        return hex2bin($filename);
    }
}
