<?php

/**
 * 箇条書きクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019,2026 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * - One
 *  - Two
 *   - Three.
 */
class UnorderedList extends ListContainer
{
    public function __construct($root, $text, $page)
    {
        parent::__construct('ul', 'li', '-', $text);
    }
}
