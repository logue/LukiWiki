<?php
/**
 * 水平線クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * Horizontal Rule.
 */
class HRule extends Element
{
    public function toString()
    {
        return '<hr />';
    }
}
