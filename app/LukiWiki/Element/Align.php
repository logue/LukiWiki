<?php
/**
 * 位置決めクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * LEFT: / CENTER: / RIGHT: / JUSTIFY:.
 */
class Align extends Element
{
    protected $align;

    public function __construct($align)
    {
        parent::__construct();
        $this->align = $align;
    }

    public function canContain(&$obj)
    {
        if ($obj instanceof Table || $obj instanceof YTable) {
            $obj->align = $this->align;
        }

        return $obj instanceof InlineElement;
    }

    public function toString()
    {
        if (empty($this->align)) {
            return $this->wrap(parent::toString(), 'div');
        }
        $align = strtolower($this->align);

        return $this->wrap(parent::toString(), 'div', ' class="text-'.$this->align.'"');
    }
}
