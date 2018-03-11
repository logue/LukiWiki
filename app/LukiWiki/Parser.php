<?php
/**
 * WikiテキストをHTMLに変換する.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki;

use App\LukiWiki\Element\RootElement;

/**
 * パーサー.
 */
class Parser
{
    private static $instance = 0;

    /**
     * LukiWikiファクトリークラス.
     *
     * @param mixed $lines Wikiのソース
     * @param bool  $isAmp AMP対応フラグ
     *
     * @return string
     */
    public static function factory($lines, $isAmp = false)
    {
        if (!is_array($lines)) {
            // 改行を正規化
            $lines = explode("\n", str_replace([chr(0x0d).chr(0x0a), chr(0x0d), chr(0x0a)], "\n", $lines));
        }

        $body = new RootElement(null, null, ['id' => ++self::$instance]);
        $body->parse($lines);
        dd($body);

        return $body->toString();
    }
}
