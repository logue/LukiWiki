<?php
/**
 * リスト要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

class ListElement extends Element
{
    public function __construct($level, $head)
    {
        parent::__construct();
        $this->level = $level;
        $this->head = $head;
    }

    public function canContain($obj)
    {
        return !$obj instanceof ListContainer || $obj->level > $this->level;
    }

    public function __toString()
    {
        return $this->wrap(parent::__toString(), $this->head, [], false);
    }
}
