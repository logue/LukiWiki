<?php
/**
 * インライン要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\Inline\InlineConverter;

/**
 * Inline elements.
 */
class InlineElement extends Element
{
    private static $converter;

    public function __construct($text)
    {
        parent::__construct();
        $text = trim($text);

        if (substr($text, 0, 1) === "\n") {
            $this->elements[] = $text;
        } else {
            if (!isset(self::$converter)) {
                self::$converter = new InlineConverter();
            }
            $clone = self::$converter->getClone(self::$converter);
            $this->elements[] = $clone->convert($text);
        }
    }

    public function insert($obj)
    {
        $this->elements[] = $obj->elements[0];

        return $this;
    }

    public function canContain($obj)
    {
        return $obj instanceof self;
    }

    public function toString()
    {
        return implode("\n", $this->elements);
    }

    public function toPara($class = '')
    {
        $obj = new Paragraph(null, $class);
        $obj->insert($this);

        return $obj;
    }
}
