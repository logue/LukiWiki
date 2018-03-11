<?php
/**
 * 整形済みテキストクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * ' 'Space-beginning sentence.
 */
class Pre extends Element
{
    public function __construct($root, $text)
    {
        parent::__construct();
        $this->elements[] = parent::processText(empty($text) || $text[0] !== ' ' ? $text : substr($text, 1));
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
        return $this->wrap(implode('', $this->elements), 'pre');
    }
}
