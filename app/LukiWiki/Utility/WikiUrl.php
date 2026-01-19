<?php

/**
 * Wikiのファイルシステム関数.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018,2026 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Utility;

use Illuminate\Support\Facades\Config;

class WikiUrl
{
    /**
     * 相対指定のページ名から全ページ名を取得.
     *
     * @param  string  $name  名前の入力値
     * @param  string  $refer  引用元のページ名
     * @return string ページのフルパス
     */
    public static function getFullName(string $name, ?string $refer = null)
    {
        $defaultpage = Config::get('lukiwiki.defaultpage');
        // 'Here'
        if (empty($name) || $name === './') {
            // ページ名が指定されてない場合、引用元のページ名を返す
            return $refer;
        }

        // Absolute path
        if ($name[0] === '/') {
            $name = substr($name, 1);

            return empty($name) ? $defaultpage : $name;
        }

        // Relative path from 'Here'
        if (substr($name, 0, 2) === './') {
            // 同一ディレクトリ
            $arrn = preg_split('#/#', $name, -1, PREG_SPLIT_NO_EMPTY);
            $arrn[0] = $refer;

            return implode('/', $arrn);
        }

        // Relative path from dirname()
        if (substr($name, 0, 3) === '../') {
            // 上の階層
            $arrn = preg_split('#/#', $name, -1, PREG_SPLIT_NO_EMPTY);
            $arrp = preg_split('#/#', $refer, -1, PREG_SPLIT_NO_EMPTY);

            // 階層を遡る
            while (! empty($arrn) && $arrn[0] === '..') {
                array_shift($arrn);
                array_pop($arrp);
            }
            // ディレクトリを結合する
            $name = ! empty($arrp) ? implode('/', array_merge($arrp, $arrn)) :
                (! empty($arrn) ? $defaultpage.'/'.implode('/', $arrn) : $defaultpage);
        }

        return $name;
    }

    /**
     * 値からパス制御文字を取り除いて基準名を取得.
     *
     * @return string;
     */
    public static function stripRelativePath(string $str)
    {
        return preg_replace('/^.*\//', '', $str);
    }
}
