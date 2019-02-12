<?php
/**
 * Preformatted Text.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018-2019 Logue
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
            list($this->meta['lang'], $this->meta['name']) = explode(':', $meta);
        } else {
            $this->meta['lang'] = $meta;
            $this->meta['name'] = '';
        }

        $this->elements[] = $text;
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
        $content = self::processText(implode("\n", $this->elements));
        if (empty($this->meta['lang'])) {
            return $this->wrap($content, 'pre', ['class' => 'CodeMirror'], false);
        }

        return $this->wrap($content, 'pre', ['v-lw-sh' => null, 'class' => 'CodeMirror', 'data-lang' => $this->meta['lang']], false);
    }
}
