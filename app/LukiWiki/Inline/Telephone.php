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
    public function __toString()
    {
        return '<a href="tel:'.$this->name.'" rel="nofollow"><font-awesome-icon fas icon="phone" class="mr-1"></font-awesome-icon>'.$this->alias.'</a>';
    }

    public function getPattern()
    {
        $s1 = $this->start + 1;

        return
            '(?:(?:\['.
                '(.[^\]\[]+)'.                          // [1] alias
            '\])'.
            '(?:'.
                '\('.
                    'tel:(([0-9]+-?)?[0-9]+-?[0-9]+)'.  // [2] telephone
                '\)'.
            ')'.
            '(?:\{'.
                '(.*[^\}]?)'.                           // [3] Body (option)
            '\})?)';
    }

    public function getCount()
    {
        return 3;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list($alias, $tel) = $this->splice($arr);
        $name = $orginalname = $tel;

        return parent :: setParam($page, $name, '', $alias === '' ? $orginalname : $alias);
    }
}
