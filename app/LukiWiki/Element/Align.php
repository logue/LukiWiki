<?php
/**
 * 位置決めクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\Rules\Alignment;

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

    public function canContain($obj)
    {
        if ($obj instanceof Table || $obj instanceof YTable) {
            $obj->align = $this->align;
        }

        return $obj instanceof InlineElement;
    }

    public function __toString()
    {
        if (empty($this->align)) {
            return $this->wrap(parent::__toString(), 'div', [], false);
        }

        return $this->wrap(parent::__toString(), 'div', ['class' => Alignment::block($this->align)], false);
    }
}
