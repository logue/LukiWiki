<?php
/**
 * 引用ブロッククラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;

/**
 * > Someting cited
 * > like E-mail text.
 */
class Blockquote extends AbstractElement
{
    protected $level;

    public function __construct($root, $text, $isAmp)
    {
        parent::__construct();

        $head = $text[0];
        $this->level = min(3, strspn($text, $head));
        $text = ltrim(substr($text, $this->level));

        $content = new InlineElement($text, $isAmp);
        $this->meta = $content->getMeta();

        if ($head === '<') { // Blockquote close
            $level = $this->level;
            $this->level = 0;
            $this->last = $this->end($root, $level);
            if (!empty($text)) {
                $this->last = $this->last->insert($content);
            }
        } else {
            $this->insert($content);
        }
    }

    public function __toString()
    {
        return $this->wrap(parent::__toString(), 'blockquote', ['class' => 'blockquote'], false);
    }

    public function canContain($obj)
    {
        return !($obj instanceof self) || $obj->level >= $this->level;
    }

    public function insert($obj)
    {
        if (!\is_object($obj)) {
            return;
        }

        if ($obj instanceof InlineElement) {
            return parent::insert($obj);
        }

        if ($obj instanceof self && $obj->level === $this->level && \count($obj->elements)) {
            $obj = $obj->elements[0];
            if ($this->last instanceof Paragraph && \count($obj->elements)) {
                $obj = $obj->elements[0];
            }
        }

        return parent::insert($obj);
    }

    private function end($root, $level)
    {
        $parent = $root->last;

        while (\is_object($parent)) {
            if ($parent instanceof self && $parent->level === $level) {
                return $parent->parent;
            }
            $parent = $parent->parent;
        }

        return $this;
    }
}
