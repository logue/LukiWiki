<?php
/**
 * インライン要素変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\Rules\InlineRules;

/**
 * Converters of inline element.
 */
class InlineConverter
{
    /**
     * デフォルトの変換パターン.
     */
    private static $default_converters = [
        'App\LukiWiki\Inline\Media',            // Media Link: ![alt](url file "title"){option}
        'App\LukiWiki\Inline\BracketName',      // AutoLink

        'App\LukiWiki\Inline\Note',             // Footnotes
        'App\LukiWiki\Inline\AutoLink',         // AutoLink
        'App\LukiWiki\Inline\InterWiki',        // AutoLink
        'App\LukiWiki\Inline\Mailto',           // mailto: URL schemes
        //'App\LukiWiki\Inline\InterWikiName',    // InterWikiName
        'App\LukiWiki\Inline\Telephone',        // tel: URL schemes
        'App\LukiWiki\Inline\Link',             // Link: [alt](media file "title"){option}
        'App\LukiWiki\Inline\InlinePlugin',     // Inline plugins
    ];
    /**
     * 変換クラス.
     */
    private $converters = [];
    /**
     * 変換処理に用いる正規表現パターン.
     */
    private $pattern;

    private static $clone_func;

    private $meta;

    public $page;

    /**
     * コンストラクタ
     *
     * @param array  $converters 使用する変換クラス名
     * @param array  $excludes   除外する変換クラス名
     * @param string $page       ページ名
     */
    public function __construct(array $converter = [], array $excludes = [], ?string $page = null)
    {
        static $converters;
        if (!isset($converters)) {
            $converters = self::$default_converters;
        }
        // 除外するクラス
        if ($excludes !== null) {
            $converters = array_diff($converters, $excludes);
        }

        //dd($page);

        $this->converters = $patterns = [];
        $start = 1;

        foreach ($converters as $name) {
            if (empty($name)) {
                continue;
            }

            $converter = new $name($start, $page);

            $pattern = $converter->getPattern();
            if (empty($pattern)) {
                continue;
            }
            //echo $name."\n";

            $patterns[] = '('.$pattern.')';
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
    public function getClone(object $obj)
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
     * WikiをHTMLに変換するメイン処理.
     *
     * @param string $string
     *
     * @return string
     */
    public function convert(string $string)
    {
        $input = htmlspecialchars($string, ENT_HTML5, 'UTF-8');
        if (empty($input)) {
            return;
        }
        $this->result = [];
        $page = $this->page;
        $string = preg_replace_callback('/'.$this->pattern.'/ux', function ($arr) use ($page) {
            $obj = $this->getConverter($arr);

            if ($obj !== null) {
                $this->result[] = ($obj->setPattern($arr, $page) !== false) ? $obj->__toString() : $arr[0];
                $this->meta = $obj->getMeta();
            } else {
                $this->result[] = $arr[0];
            }

            return "\x08"; // Add a mark into latest processed part
        }, $string);

        $arr = explode("\x08", InlineRules::replace($string));
        $retval = [];
        while (!empty($arr)) {
            $retval[] = trim(array_shift($arr).array_shift($this->result));
        }

        return trim(implode('', $retval));
    }

    /**
     * 変換クラスを取得.
     *
     * @param array $arr
     *
     * @return object
     */
    private function getConverter(array $arr)
    {
        foreach (array_keys($this->converters) as $start) {
            if (isset($arr[$start]) && $arr[$start] === $arr[0]) {
                return $this->converters[$start];
            }
        }
    }

    /**
     * メタ情報を取得.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
