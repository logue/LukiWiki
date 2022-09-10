<?php

/**
 * WikiテキストをHTMLに変換する.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki;

use App\LukiWiki\Element\RootElement;

/**
 * パーサー.
 */
class Parser
{
    /** @var string */
    const VERSION = '0.0.0-alpha';

    /** @var int インスタンス */
    private static $instance = 0;

    /**
     * LukiWikiファクトリークラス.
     *
     * @param  string  $source Wikiのソース
     * @param  string  $page   呼び出し元ページ名
     * @return object
     */
    public static function factory(string $source, string $page)
    {
        $instance = ++self::$instance;

        clock()->event(`Compile Wiki data. [$instance]`)->begin();
        $lines = explode("\n", str_replace([\chr(0x0D).\chr(0x0A), \chr(0x0D), \chr(0x0A)], "\n", $source));
        $body = new RootElement($page, ['id' => $instance]);
        $body->parse($lines);
        clock()->event(`Compile Wiki data. [$instance]`)->end();

        return $body;
    }
}
