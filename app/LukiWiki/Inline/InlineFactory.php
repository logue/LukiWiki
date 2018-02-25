<?php
/**
 * インライン変換ファクトリークラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

class InlineFactory
{
    private static $converter;

    public static function factory($string, $page = '')
    {
        global $vars;

        if (is_array($string)) {
            $string = implode("\n", $string);
        }	// ポカミス用

        if (!isset(self::$converter)) {
            self::$converter = new InlineConverter();
        }
        $clone = self::$converter->getClone(self::$converter);

        return $clone->convert($string, isset($vars['page']) ? $vars['page'] : $page);
    }
}
