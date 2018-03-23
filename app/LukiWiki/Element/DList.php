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
    public function __construct($out, $isAmp)
    {
        parent::__construct('dl', 'dt', ':', $out[0]);
        $element = new ListElement($this->level, 'dd');
        $element->isAmp = $isAmp;
        $this->last = Element::insert($element);

        if (!empty($out[1])) {
            $content = new InlineElement($out[1], $isAmp);
            $this->meta = $content->getMeta();
            $this->last = $this->last->insert($content);
        }
    }
}
