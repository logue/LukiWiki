<?php
/**
 * 定義文クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * : definition1 | description1
 * : definition2 | description2
 * : definition3 | description3.
 */
class DList extends ListContainer
{
    public function __construct($out)
    {
        parent::__construct('dl', 'dt', ':', $out[0]);
        $element = new ListElement($this->level, 'dd');
        $this->last = Element::insert($element);
        if (!empty($out[1])) {
            $this->last = $this->last->insert(ElementFactory::factory('InlineElement', null, $out[1]));
        }
    }
}
