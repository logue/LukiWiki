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
use Debugbar;

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
     * @param string $source Wikiのソース
     * @param string $page   呼び出し元ページ名
     *
     * @return object
     */
    public static function factory(string $source, string $page)
    {
        $instance = ++self::$instance;

        Debugbar::startMeasure('parse', 'Converting wiki data... ['.$instance.']');
        $lines = explode("\n", str_replace([\chr(0x0d).\chr(0x0a), \chr(0x0d), \chr(0x0a)], "\n", $source));
        $body = new RootElement($page, ['id' => $instance]);
        $body->parse($lines);
        Debugbar::stopMeasure('parse');

        return $body;
    }
}
