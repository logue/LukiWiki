<?php
/**
 * 水平線クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;

/**
 * Horizontal Rule.
 */
class HorizontalRule extends AbstractElement
{
    public function __toString()
    {
        return '<hr />';
    }
}
