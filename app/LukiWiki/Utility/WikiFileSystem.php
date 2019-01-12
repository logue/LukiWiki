<?php
/**
 * Wikiのファイルシステム関数.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Utility;

use Debugbar;
use FlorianWolters\Component\Util\Singleton\SingletonTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
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

    // RFC3986で定義されているページ名として使用できない文字＋α
    const RESERVED_CHARS = ['!', '#', '$', '&', '\'', '(', ')', '*', '+', ',', '/', ':', ';', '=', '?', '@', '[', ']', '~'];

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
        }

        if (Storage::getMetadata($directory)['type'] !== 'dir') {
            // ディレクトリでない
            throw new \Exception('Not a directory.');
        }

        if (Storage::lastModified($directory) > (int) Cache::get(self::CACHE_DATE_PREFIX.self::TYPE)) {
            // ディレクトリの更新日時がキャッシュ生成日時よりも新しい場合、キャッシュを削除
            Cache::forget(self::DIRECTORY_CACHE_PREFIX.self::TYPE);
            Cache::forget(self::MATCH_PATTERN_CACHE_PREFIX.self::TYPE);
            Cache::forget(self::CACHE_DATE_PREFIX.self::TYPE);
        }
    }

    /**
     * ページを保存.
     *
     * @param string $page    ページ名
     * @param string $content 内容
     *
     * @return bool
     */
    public function __set(string $page, string $content)
    {
        if (!self::isValiedPageName($page)) {
            return false;
        }
        // タイムスタンプとハッシュが変わるのでキャッシュを削除
        Cache::forget(self::CACHE_DATE_PREFIX.self::TYPE);

        return Storage::put(self::getFilePath($page), self::convertEOL($content));
    }

    /**
     * ページを取得.
     *
     * @param string $page ページ名
     *
     * @return string|bool
     */
    public function __get(string $page)
    {
        Debugbar::startMeasure('loading', 'Loading '.$page.'...');
        if (!self::isValiedPageName($page)) {
            return false;
        }
        $path = self::getFilePath($page);

        if (!Storage::exists($path)) {
            return false;
        }

        $data = trim(Storage::get($path));

        Debugbar::stopMeasure('loading');

        return $data;
    }

    /**
     * ページの存在確認.
     *
     * @param string $page ページ名
     *
     * @return bool
     */
    public function __isset(string $page)
    {
        if (!self::isValiedPageName($page)) {
            return false;
        }

        return Storage::exists(self::getFilePath($page));
    }

    /**
     * ページの削除.
     *
     * @param string $page ページ名
     */
    public function __unset(string $page)
    {
        if (!self::isValiedPageName($page)) {
            return false;
        }
        $path = self::getFilePath($page);

        try {
            Cache::forget(self::CACHE_DATE_PREFIX.self::TYPE);

            return Storage::delete(self::getFilePath($page));
        } catch (\Exception $e) {
            throw new FileNotFoundException($path.'('.$page.') is not found.', $e->getCode(), $e);

            return false;
        }
    }

    /**
     * ページのリネーム.
     *
     * @param string $from 変更前
     * @param string $to   変更後
     *
     * @return bool
     */
    public function rename(string $from, string $to)
    {
        if (!self::isValiedPageName($from) || !self::isValiedPageName($to)) {
            return false;
        }

        Cache::forget(self::CACHE_DATE_PREFIX.self::TYPE);

        return Storage::move(self::getFilePath($from), self::getFilePath($to));
    }

    /**
     * ページの最終更新日.
     * 空白時はディレクトリの最終更新日時
     *
     * @param string $page
     *
     * @return int
     */
    public function timestamp(string $page = null)
    {
        if (empty($page)) {
            return Storage::lastModified(Config::get('lukiwiki.directory.'.self::TYPE));
        }

        if (!isset($this->$page)) {
            return;
        }

        return Storage::lastModified(self::getFilePath($page));
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
        Debugbar::startMeasure('listing', 'Process data directory');
        $list = Cache::rememberForever(self::DIRECTORY_CACHE_PREFIX.self::TYPE, function () {
            // ファイル名一覧の処理はキャッシュに入れる
            $ret = [];
            $files = Storage::files(Config::get('lukiwiki.directory.'.self::TYPE));
            foreach ($files as &$filepath) {
                $page = self::decode($filepath);
                if (empty($page)) {
                    // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
                    continue;
                }

                $ret[$page] = [
                    // ファイルのパス
                    'path' => $filepath,
                    // ファイルの更新日
                    'timestamp' => $this->timestamp($page),
                ];
                unset($filepath);
            }

            // キャッシュの更新日時を更新
            Cache::forever(self::CACHE_DATE_PREFIX.self::TYPE, Storage::lastModified(Config::get('lukiwiki.directory.'.self::TYPE)));

            return $ret;
        });
        Debugbar::stopMeasure('listing');

        return $list;
    }

    /**
     * ページ一覧の正規表現を出力.
     *
     * @return string
     */
    public function __toString()
    {
        return Cache::rememberForever(self::MATCH_PATTERN_CACHE_PREFIX.self::TYPE, function () {
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
    private static function getFileName(string $path)
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
    private static function getFilePath(string $page)
    {
        return Config::get('lukiwiki.directory.'.self::TYPE).DIRECTORY_SEPARATOR.self::encode($page).self::EXTENTION;
    }

    /**
     * ページ名をWikiのファイル名に変更.
     *
     * @param string $page ページ名
     *
     * @return string
     */
    public static function encode(string $page)
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
    public static function decode(string $filepath)
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

    /**
     * 無効なページ名が指定されていないかをチェック.
     *
     * @param string $page
     *
     * @return bool
     */
    protected static function isValiedPageName(string $page)
    {
        // 多分正規表現より早い
        foreach (self::RESERVED_CHARS as &$niddle) {
            if ((strpos($page, $niddle)) !== false) {
                return false;
            }
            unset($niddle);
        }

        return true;
    }

    protected static function convertEOL($str, $to = "\n")
    {
        $str = str_replace([chr(0x0d).chr(0x0a), chr(0x0d), chr(0x0a)], "\n", $str);
        if ("\n" !== $to) {
            $str = str_replace("\n", $to, $str);
        }

        return $str;
    }
}
