<?php
/**
 * インライン要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\Inline\InlineFactory;

/**
 * Inline elements.
 */
class InlineElement extends Element
{
    public function __construct($text)
    {
        parent::__construct();
        $this->elements[] = trim((substr($text, 0, 1) === "\n") ?
            $text : InlineFactory::factory($text));
    }

    public function insert(&$obj)
    {
        $this->elements[] = $obj->elements[0];

        return $this;
    }

    public function canContain(&$obj)
    {
        return $obj instanceof self;
    }

    public function toString()
    {
        // 改行を<br />に変換するか？
        return nl2br(implode("\n", $this->elements));
    }

    public function toPara($class = '')
    {
        $obj = new Paragraph(null, $class);
        $obj->insert($this);

        return $obj;
    }
}
