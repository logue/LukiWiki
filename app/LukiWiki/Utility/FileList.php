<?php
/**
 * ディレクトリ内のファイル一覧取得.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Utility;

use Illuminate\Support\Facades\Storage;

class FileList
{
    /**
     * コンストラクタ
     *
     * @param array $dir ディレクトリのオブジェクト
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * ファイル名とページ名の対応リストを取得する.
     *
     * @param string $type ファイルの種類
     *
     * @return array
     */
    public function getList($type)
    {
        if (Storage::getMetadata($this->dir[$type])['type'] !== 'dir') {
            throw new \Exception('directory is not found.');
        }

        $files = Storage::files($this->dir[$type]);

        if ($type === 'attachment') {
            throw new \Exeption('Attachment file list does not support this method. Use getAttachmentList() method.');
        }

        $ret = [];
        foreach ($files as $filepath) {
            $page = self::decode($filepath);

            if (empty($page)) {
                // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
                continue;
            }

            $ret[$page] = $filepath;
        }

        return $ret;
    }

    /**
     * 添付ファイル一覧
     *（とても遅い）.
     */
    public function getAttachmentList()
    {
        $ret = [];
        foreach (Storage::files($this->dir['attachment']) as $filepath) {
            $matches = [];
            $file = self::getFileName($filepath);
            if (preg_match('/^((?:[0-9A-F]{2})+)_((?:[0-9A-F]{2})+)(?:\.([0-9|log]+))?$/', $file, $matches)) {
                // ページ名
                $page = self::decode($matches[1]);
                // 添付ファイル名
                $attach = self::decode($matches[2]);
                // 添付ファイルの世代（およびログ）
                $generation = $matches[3] ?? 0;

                $ret[$page] = [
                    $generation => $attach,
                ];
            }
        }
    }

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
