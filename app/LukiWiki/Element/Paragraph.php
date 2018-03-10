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
class Paragraph extends Element
{
    public function __construct($text)
    {
        parent::__construct();

        if (substr($text, 0, 1) === '~') {
            $text = ' '.substr($text, 1);
        }

        $this->insert(new InlineElement($text));
    }

    public function canContain($obj)
    {
        return $obj instanceof InlineElement;
    }

    public function toString()
    {
        return $this->wrap(parent::toString(), 'p');
    }
}
