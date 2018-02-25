<?php
/**
 * インライン要素変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

/**
 * Converters of inline element.
 */
class InlineConverter
{
    /**
     * デフォルトの変換パターン.
     */
    private static $default_converters = [
        'App\LukiWiki\Inline\InlinePlugin',     // Inline plugins
        'App\LukiWiki\Inline\Note',             // Footnotes
    //    'App\LukiWiki\Inline\Url',              // URLs
        'App\LukiWiki\Inline\InterWiki',        // URLs (interwiki definition)
    //    'App\LukiWiki\Inline\Mailto',           // mailto: URL schemes
    //    'App\LukiWiki\Inline\InterWikiName',    // InterWikiName
        'App\LukiWiki\Inline\BracketName',      // BracketName
    //    'App\LukiWiki\Inline\WikiName',         // WikiName
    //    'App\LukiWiki\Inline\AutoLink',         // AutoLink(cjk,other)
    //    'App\LukiWiki\Inline\AutoLink_Alphabet',    // AutoLink(alphabet)
    //    'App\LukiWiki\Inline\Telephone',        // tel: URL schemes
    ];
    /**
     * 変換クラス.
     */
    private $converters = [];
    /**
     * 変換処理に用いる正規表現パターン.
     */
    private $pattern;
    /**
     * 結果.
     */
    private $result = [];

    private static $clone_func;

    /**
     * コンストラクタ
     *
     * @param array $converters 使用する変換クラス名
     * @param array $excludes   除外する変換クラス名
     */
    public function __construct($converters = [], $excludes = [])
    {
        static $converters;
        if (!isset($converters)) {
            $converters = self::$default_converters;
        }
        // 除外する
        if ($excludes !== null) {
            $converters = array_diff($converters, $excludes);
        }

        $this->converters = $patterns = [];
        $start = 1;

        foreach ($converters as $name) {
            if (!isset($name)) {
                continue;
            }

            $converter = new $name($start);

            $pattern = $converter->getPattern();
            if ($pattern === false) {
                continue;
            }

            $patterns[] = '('."\n".$pattern."\n".')';
            $this->converters[$start] = $converter;
            $start += $converter->getCount();
            ++$start;
        }
        $this->pattern = implode('|', $patterns);
    }

    /**
     * 関数のクローン.
     */
    public function __clone()
    {
        $converters = [];
        foreach ($this->converters as $key => $converter) {
            $converters[$key] = $this->getClone($converter);
        }
        $this->converters = $converters;
    }

    /**
     * クローンした関数を取得.
     *
     * @staticvar type $clone_func
     *
     * @param object $obj オブジェクト名
     *
     * @return function
     */
    public function getClone($obj)
    {
        static $clone_func;

        if (!isset($clone_func)) {
            $clone_func = function ($a) {
                return clone $a;
            };
        }

        return $clone_func($obj);
    }

    /**
     * 変換.
     *
     * @param string $string
     * @param string $page
     *
     * @return type
     */
    public function convert($string, $page)
    {
        $this->page = $page;

        $string = preg_replace_callback('/'.$this->pattern.'/x', function ($arr) {
            $obj = $this->getConverter($arr);

            $this->result[] = ($obj !== null && $obj->setPattern($arr, $this->page) !== false) ?
                $obj->__toString() : Inline::setLineRules(htmlspecialchars($arr[0], ENT_HTML5, 'UTF-8'));

            return "\x08"; // Add a mark into latest processed part
        }, $string);

        $arr = explode("\x08", $string);
        $retval = null;
        while ($arr) {
            $retval .= array_shift($arr).array_shift($this->result);
        }

        return trim($retval);
    }

    /**
     * オブジェクトを取得.
     *
     * @param string $string
     * @param string $page
     *
     * @return array
     */
    public function getObjects($string, $page)
    {
        $matches = $arr = [];
        preg_match_all('/'.$this->pattern.'/x', $string, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $obj = $this->getConverter($match);
            if ($obj->setPattern($match, $page) !== false) {
                $arr[] = $this->getClone($obj);
                if (!empty($obj->body)) {
                    $arr = array_merge($arr, $this->getObjects($obj->body, $page));
                }
            }
        }

        return $arr;
    }

    /**
     * 変換クラスを取得.
     *
     * @param array $arr
     *
     * @return object
     */
    private function getConverter($arr)
    {
        foreach (array_keys($this->converters) as $start) {
            if (isset($arr[$start]) && $arr[$start] === $arr[0]) {
                return $this->converters[$start];
            }
        }
    }
}
