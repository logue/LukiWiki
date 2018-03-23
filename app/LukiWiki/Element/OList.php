<?php
/**
 * 番号付きリスト要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * + One
 * + Two
 * + Three.
 */
class OList extends ListContainer
{
    public function __construct($root, $text, $isAmp)
    {
        parent::__construct('ol', 'li', '+', $text, $isAmp);
    }
}
