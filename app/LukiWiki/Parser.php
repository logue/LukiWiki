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

    public static function factory($lines)
    {
        if (!is_array($lines)) {
            // 改行を正規化
            $lines = explode("\n", str_replace([chr(0x0d).chr(0x0a), chr(0x0d), chr(0x0a)], "\n", $lines));
        }

        $body = new RootElement(null, null, ['id' => ++self::$instance]);
        $body->parse($lines);

        return $body->toString();
    }
}
