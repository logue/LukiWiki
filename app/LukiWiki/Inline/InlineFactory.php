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
        if (!isset(self::$converter)) {
            self::$converter = new InlineConverter();
        }
        $clone = self::$converter->getClone(self::$converter);

        return $clone->convert($string, $page);
    }
}
