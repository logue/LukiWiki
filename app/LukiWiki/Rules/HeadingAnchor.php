<?php

/**
 * 見出しのIDクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Rules;

class HeadingAnchor
{
    /**
     * 見出しの固有IDのマッチパターン.
     */
    private const HEADING_ID_PATTERN = '/^(\#{1,5})\s(.*?)\s(?:\[\#(\w+)\]\s*)?$/m';
    /**
     * 見出しのIDの生成で使用出来る文字.
     */
    private const HEADING_ID_ACCEPT_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    /**
     * 見出し判別IDで使用する文字数.
     */
    private const HEADING_ID_LENGTH = 7;

    /**
     * 見出しのIDを作る.
     *
     * @param string $str  入力文字列
     * @param string $id   見出しのID
     * @param mixed  $line
     *
     * @return string
     */
    public static function set($line, $id = null)
    {
        $matches = [];
        if (preg_match(self::HEADING_ID_PATTERN, $line, $matches) && (!isset($matches[3]) || empty($matches[3]))) {
            // 7桁のランダム英数字をアンカー名として表題の末尾に付加
            $line = rtrim($matches[1] . $matches[2]) . ' [' . (empty($id) ? substr(str_shuffle(self::HEADING_ID_ACCEPT_CHARS), 0, self::HEADING_ID_LENGTH) : $id) . ']';
        }

        return $line;
    }

    /**
     * 見出しからIDを取得.
     *
     * @param string $str   入力文字列
     * @param bool   $strip 見出し編集用のアンカーを削除する
     *
     * @return string
     */
    public static function get($str, $strip = true)
    {
        // Cut fixed-heading anchors
        $id = $heading = '';
        $matches = [];
        if (preg_match(self::HEADING_ID_PATTERN, $str, $matches)) { // 先頭が#から始まってて、なおかつ[#...]が存在する
            $level = substr_count($matches[1], '#');
            $heading = trim($matches[2]);
            $id = isset($matches[3]) ? $matches[3] : null;
        } else {
            $heading = preg_replace('/^\#{0,5}/', '', $str);
            $level = 0;
        }

        // Cut footnotes and tags
        if ($strip === true) {
            $heading = strip_tags(
                InlineFactory::factory(preg_replace('/' . InlineRules::NOTE_PATTERN . '/x', '', $heading))
            );
        }

        return [$heading, $id, $level];
    }

    /**
     * 見出しIDを削除.
     *
     * @param string $str
     *
     * @return string
     */
    public static function remove($str)
    {
        return preg_replace_callback(
            self::HEADING_ID_PATTERN,
            function ($matches) {
                return $matches[2];
            },
            $str
        );
    }
}
