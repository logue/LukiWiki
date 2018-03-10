<?php
/**
 * #が先頭に来るタイプの整形済みテキストクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\Inline\InlineFactory;

/**
 * ' 'Space-beginning sentence with color(started with '# ')
 * ' 'Space-beginning sentence with color
 * ' 'Space-beginning sentence with color.
 */
class SharpPre extends Element
{
    public function __construct($root, $text)
    {
        parent::__construct();
        if (substr($text, 0, 2) === '# ') {
            $text = substr($text, 1);
        }
        $this->elements[] = (empty($text) || substr($text, 0, 1) !== ' ') ? $text : substr($text, 1);
    }

    public function canContain($obj)
    {
        return $obj instanceof self;
    }

    public function insert($obj)
    {
        $this->elements[] = $obj->elements[0];

        return $this;
    }

    public function toString()
    {
        // 変換処理
        $ret = InlineFactory::factory($this->elements);

        return $this->wrap($ret, 'pre');
    }
}
