<?php
/**
 * Preformatted Text.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;

/**
 * ```lang ... ```.
 */
class PreformattedText extends AbstractElement
{
    private $lang;

    public function __construct($root, $text, $meta)
    {
        parent::__construct();
        if (strpos($meta, ':')) {
            list($lang, $name) = explode(':', $meta);
        } else {
            $lang = $meta;
            $name = '';
        }
        $this->meta = ['lang' => $lang, 'name' => $name];

        $this->elements[] = parent::processText($text);
    }

    public function canContain($obj)
    {
        return $obj instanceof self;
    }

    public function insert($obj)
    {
        $this->elements[] = $obj->elements[0];

        return $this;
    }

    public function __toString()
    {
        return $this->wrap(implode("\n", $this->elements), 'pre', ['class' => 'CodeMirror', 'data-lang' => $this->meta['lang']], false);
    }
}
