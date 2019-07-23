<?php
/**
 * 定義文クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018,2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;

/**
 * : definition1 | description1
 * : definition2 | description2
 * : definition3 | description3.
 */
class DefinitionList extends ListContainer
{
    public function __construct(string $line, string $page)
    {
        $out = explode('|', $line);
        parent::__construct('dl', 'dt', ':', $out[0]);
        $element = new ListElement($this->level, 'dd');
        $this->last = AbstractElement::insert($element);

        if (!empty($out[1])) {
            $content = new InlineElement($out[1], $page);
            $this->meta = $content->getMeta();
            $this->last = $this->last->insert($content);
        }
    }
}
