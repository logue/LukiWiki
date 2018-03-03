<?php
/**
 * Wikiのファイルシステム関数.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Utility;

use FlorianWolters\Component\Util\Singleton\SingletonTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RegexpTrie\RegexpTrie;

class WikiFileSystem
{
    use SingletonTrait;

    // キャッシュ名のプレフィックス
    const DIRECTORY_CACHE_PREFIX = 'directory-';
    // キャッシュ名のプレフィックス
    const MATCH_PATTERN_CACHE_PREFIX = 'autolink-';
    // キャッシュ名のプレフィックス
    const CACHE_DATE_PREFIX = 'cache-date-directory-';

    // ファイルの種類
    const TYPE = 'data';
    // ファイルの拡張子
    const EXTENTION = '.txt';

    /** ディレクトリのパス */
    private $directory;
    /** ディレクトリの更新日（ファイル一覧キャッシュなどで使用） */
    private $modified;
    /** 一覧キャッシュファイル名 */
    private $directory_cache_name;
    /** 一覧キャッシュファイル名 */
    private $pattern_cache_name;

    /**
     * コンストラクタ
     */
    private function __construct()
    {
        // 設定を取得
        $directory = Config::get('lukiwiki.directory.'.self::TYPE);
        if (empty($directory)) {
            // 設定がない
            throw new \Exception('Not defineded.');
        }

        if (!Storage::exists($directory)) {
            // ディレクトリがない
            Storage::makeDirectory($directory);
        } else {
            $this->modified = Storage::lastModified($directory);
        }
        if (Storage::getMetadata($directory)['type'] !== 'dir') {
            // ディレクトリでない
            throw new \Exception('Not a directory.');
        }

        $this->directory = $directory;
        $this->directory_cache_name = self::DIRECTORY_CACHE_PREFIX.self::TYPE;
        $this->pattern_cache_name = self::MATCH_PATTERN_CACHE_PREFIX.self::TYPE;
        $this->cache_date_name = self::CACHE_DATE_PREFIX.self::TYPE;

        if ($this->modified > (int) Cache::get($this->cache_date_name)) {
            // ディレクトリの更新日時がキャッシュ生成日時よりも新しい場合、キャッシュを削除
            Cache::forget($this->directory_cache_name);
            Cache::forget($this->pattern_cache_name);
            Cache::forget($this->cache_date_name);
        }
    }

    /**
     * ページを保存.
     *
     * @param string $page    ページ名
     * @param string $content 内容
     */
    public function __set($page, $content)
    {
        // タイムスタンプとハッシュが変わるのでキャッシュを削除
        Cache::forget($this->directory_cache_name);

        Storage::put($this->getFilePath($page), $content);
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
        return trim(Storage::get($this->getFilePath($page)));
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
        return Storage::exists($this->getFilePath($page));
    }

    /**
     * ページの削除.
     *
     * @param string $page ページ名
     */
    public function __unset($page)
    {
        Cache::forget($this->directory_cache_name);
        Cache::forget($this->pattern_cache_name);

        return Storage::delete($this->getFilePath($page));
    }

    /**
     * ページのリネーム.
     *
     * @param string $from 変更前
     * @param string $to   変更後
     */
    public function rename($from, $to)
    {
        Cache::forget($this->directory_cache_name);
        Storage::move($this->getFilePath($from), $this->getFilePath($to));
    }

    /**
     * ページの最終更新日.
     *
     * @param string $page
     */
    public function modified($page)
    {
        return Storage::lastModified($this->getFilePath($page));
    }

    /**
     * ページのハッシュ.
     *
     * @param string $page
     */
    public function hash($page)
    {
        if (!isset($this->page)) {
            return;
        }

        return hash('md5', $this->page);
    }

    /**
     * タイムスタンプを更新.
     */
    public function touch($page, $time)
    {
        //return touch($this->getFilePath($page), $time);
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
        return Cache::remember($this->directory_cache_name, null, function () {
            // ファイル名一覧の処理はキャッシュに入れる
            $ret = [];
            $files = Storage::files($this->directory);
            foreach ($files as $filepath) {
                $page = self::decode($filepath);
                if (empty($page)) {
                    // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
                    continue;
                }

                $ret[$page] = [
                    // ファイルのパス
                    'path' => $filepath,
                    // ファイルの更新日
                    'modified' => $this->modified($page),
                    // MD5ハッシュ
                    'hash' => $this->hash($page),
                ];
            }

            // キャッシュの更新日時を更新
            Cache::forever($this->cache_date_name, $this->modified);

            return $ret;
        });
    }

    /**
     * ページ一覧の正規表現を出力.
     *
     * @return string
     */
    public function __toString()
    {
        return Cache::remember($this->pattern_cache_name, null, function () {
            $trie = new RegexpTrie(array_keys(self::__invoke()));

            return $trie->build();
        });
    }

    /**
     * パスからファイル名を取得.
     *
     * @param string $path ファイルパス
     *
     * @return string
     */
    private static function getFileName($path)
    {
        // パスを削除
        return substr($path, strrpos($path, '/') + 1);
    }

    /**
     * ページ名からファイル名を指定.
     *
     * @param string $page ページ名
     *
     * @return string
     */
    private function getFilePath($page)
    {
        return $this->directory.DIRECTORY_SEPARATOR.self::encode($page).self::EXTENTION;
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
        // HEXに変換して大文字にする
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
