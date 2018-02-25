<?php
/**
 * 引用ブロッククラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * > Someting cited
 * > like E-mail text.
 */
class Blockquote extends Element
{
    protected $level;

    public function __construct(&$root, $text)
    {
        parent::__construct();

        $head = substr($text, 0, 1);
        $this->level = min(3, strspn($text, $head));
        $text = ltrim(substr($text, $this->level));

        if ($head === '<') { // Blockquote close
            $level = $this->level;
            $this->level = 0;
            $this->last = $this->end($root, $level);
            if (!empty($text)) {
                $this->last = $this->last->insert(ElementFactory::factory('InlineElement', null, $text));
            }
        } else {
            $this->insert(ElementFactory::factory('InlineElement', null, $text));
        }
    }

    public function canContain(&$obj)
    {
        return !($obj instanceof self) || $obj->level >= $this->level;
    }

    public function insert(&$obj)
    {
        if (!is_object($obj)) {
            return;
        }

        // BugTrack/521, BugTrack/545
        if ($obj instanceof InlineElement) {
            return parent::insert($obj);
        }

        $class = get_class($this);

        if ($obj instanceof $class && $obj->level == $this->level && count($obj->elements)) {
            $obj = &$obj->elements[0];
            if ($this->last instanceof Paragraph && count($obj->elements)) {
                $obj = &$obj->elements[0];
            }
        }

        return parent::insert($obj);
    }

    public function toString()
    {
        return $this->wrap(parent::toString(), 'blockquote', ['class' => 'blockquote']);
    }

    private function end(&$root, $level)
    {
        $parent = &$root->last;

        while (is_object($parent)) {
            if ($parent instanceof self && $parent->level == $level) {
                return $parent->parent;
            }
            $parent = &$parent->parent;
        }

        return $this;
    }
}
