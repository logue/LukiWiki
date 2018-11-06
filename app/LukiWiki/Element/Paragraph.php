<?php
/**
 * 段落クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * Paragraph: blank-line-separated sentences.
 */
class Paragraph extends AbstractElement
{
    public function __construct($text, $isAmp)
    {
        parent::__construct();

        if (substr($text, 0, 1) === '~') {
            $text = ' '.substr($text, 1);
        }
        $obj = new InlineElement($text, $isAmp);
        $this->meta = $obj->getMeta();
        $this->insert($obj);
    }

    public function canContain($obj)
    {
        return $obj instanceof InlineElement;
    }

    public function __toString()
    {
        return $this->wrap(parent::__toString(), 'p', [], false);
    }
}
