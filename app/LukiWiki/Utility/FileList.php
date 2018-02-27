<?php

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
        $files = Storage::files($this->dir[$type]);

        if ($type === 'attachment') {
            throw new \Exeption('Attachment file list does not support this method. Use getAttachmentList() method.');
        }
        $ret = [];
        foreach ($files as $file) {
            $filename = current(
                explode('.',    // 拡張子を削除
                    ltrim($file, $this->dir[$type].'/')  // パスを削除
                )
            );
            if (empty($filename)) {
                // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
                continue;
            }

            $ret[hex2bin($filename)] = $file;
        }

        return $ret;
    }

    /**
     * 添付ファイル一覧.
     */
    public function getAttachmentList()
    {
        $ret = [];
        foreach (Storage::files($this->dir['attachment']) as $file) {
            $matches = [];
            if (preg_match('/^((?:[0-9A-F]{2})+)_((?:[0-9A-F]{2})+)(?:\.([0-9|log]+))?$/', $file, $matches)) {
                // ページ名
                $page = hex2bin($matches[1]);
                // 添付ファイル名
                $attach = hex2bin($matches[2]);
                // 添付ファイルの世代（およびログ）
                $generation = $matches[3] ?? 0;

                $ret[$page] = [
                    $generation => $attach,
                ];
            }
        }
    }

    private static function encode($page)
    {
        return strtoupper(bin2hex($page));
    }

    private static function decode($file)
    {
        return hex2bin($file);
    }
}
