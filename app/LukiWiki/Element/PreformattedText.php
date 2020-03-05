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

    public function __construct($root, $text)
    {
        parent::__construct();
        $body = explode("\r", $text);
        $meta = array_shift($body);
        if (!empty($meta)) {
            if (strpos($meta, ':')) {
                list($this->meta['lang'], $this->meta['name']) = explode(':', $meta);
            } else {
                $this->meta['lang'] = $meta;
                $this->meta['name'] = '';
            }
        }

        $this->elements[] = implode("\n", $body);
    }

    public function __toString()
    {
        $content = self::processText(implode("\n", $this->elements));
        if (empty($this->meta['lang'])) {
            return $this->wrap($content, 'pre', ['class' => 'pre'], false);
        }

        return $this->wrap($content, 'pre', ['v-lw-sh' => null, 'class' => 'pre CodeMirror', 'data-lang' => $this->meta['lang']], false);
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
}
