<?php
/**
 * 箇条書きクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * - One
 * -- Two
 * --- Three.
 */
class UList extends ListContainer
{
    public function __construct($root, $text, $isAmp)
    {
        parent::__construct('ul', 'li', '-', $text, $isAmp);
    }
}
