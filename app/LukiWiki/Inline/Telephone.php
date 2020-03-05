<?php

/**
 * 電話番号変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;

// tel: URL schemes
class Telephone extends AbstractInline
{
    protected $count = 3;

    public function __toString()
    {
        return '<a href="tel:' . $this->name . '" rel="nofollow"><font-awesome-icon fas icon="phone" class="mr-1"></font-awesome-icon>' . $this->alias . '</a>';
    }

    public function getPattern(): string
    {
        $s1 = $this->start + 1;

        return
            '(?:(?:\[' .
                '(.[^\]\[]+)' .                          // [1] alias
            '\])' .
            '(?:' .
                '\(' .
                    'tel:(([0-9]+-?)?[0-9]+-?[0-9]+)' .  // [2] telephone
                '\)' .
            ')' .
            '(?:\{' .
                '(.*[^\}]?)' .                           // [3] Body (option)
            '\})?)';
    }

    public function setPattern(array $arr, string $page = null): void
    {
        list($alias, $this->anchor) = $this->splice($arr);
        $this->name = $orginalname = $tel;
        $this->page = $page;
        $this->alias = $alias === '' ? $orginalname : $alias;
    }
}
