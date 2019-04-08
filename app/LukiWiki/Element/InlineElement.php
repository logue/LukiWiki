<?php
/**
 * インライン要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;
use App\LukiWiki\Inline\InlineConverter;

/**
 * Inline elements.
 */
class InlineElement extends AbstractElement
{
    protected $page;

    public function __construct(string $text, string $page)
    {
        parent::__construct();
        $text = parent::processText($text);
        if (substr($text, 0, 1) === "\n") {
            $this->elements[] = $text;
        } else {
            if (!isset(self::$converter)) {
                self::$converter = new InlineConverter([], [], $page);
            }

            $clone = self::$converter->getClone(self::$converter);
            $this->elements[] = $clone->convert($text);
            $this->meta = $clone->getMeta();
        }
        $this->page = $page;
    }

    public function __toString()
    {
        return implode('', $this->elements);
    }

    public function insert($obj)
    {
        if (!empty($obj->elements[0])) {
            $this->elements[] = $obj->elements[0];
        }

        return $this;
    }

    public function canContain($obj)
    {
        return $obj instanceof self;
    }

    public function toPara($class = '')
    {
        $obj = new Paragraph(null, $class);
        $obj->insert($this);

        return $obj;
    }
}
