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
    public function __construct(&$root, $text)
    {
        global $preformat_ltrim;
        parent::__construct();
        $this->elements[] = htmlspecialchars(
            (!$preformat_ltrim || empty($text) || $text[0] != ' ') ? $text : substr($text, 1),
            ENT_HTML5,
            'UTF-8'
        );
    }

    public function canContain(&$obj)
    {
        return $obj instanceof self;
    }

    public function insert(&$obj)
    {
        $this->elements[] = $obj->elements[0];

        return $this;
    }

    public function toString()
    {
        return $this->wrap(implode("\n", $this->elements), 'pre');
    }
}
