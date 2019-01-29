<?php
/**
 * リストコンテナクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;

/**
 * Lists (UL, OL, DL).
 */
class ListContainer extends AbstractElement
{
    protected $tag = 'ul';
    protected $tag2 = 'li';
    public $level = 0;

    public function __construct($tag, $tag2, $head, $text)
    {
        parent::__construct();
        $this->tag = $tag;
        $this->tag2 = $tag2;
        // $this->level = min(3, strspn($text, $head));
        $this->level = strlen(explode($head, $text)[0]) + 1;    // 識別子より前の空白の数がレベル

        $text = ltrim(substr($text, $this->level));

        $element = new ListElement($this->level, $tag2);

        parent::insert($element);
        if (!empty($text)) {
            $content = new InlineElement($text, $this->isAmp);
            $this->meta = $content->getMeta();
            $this->last = $this->last->insert($content);
        }
    }

    public function canContain($obj)
    {
        return !($obj instanceof self)
            || ($this->tag === $obj->tag && $this->level === $obj->level);
    }

    public function setParent($parent)
    {
        parent::setParent($parent);

        $step = $this->level;
        if (isset($parent->parent) && ($parent->parent instanceof self)) {
            $step -= $parent->parent->level;
        }
    }

    public function insert($obj)
    {
        if (!$obj instanceof self && $this->level > 3) {
            return $this->last = $this->last->insert($obj);
        }

        // Break if no elements found (BugTrack/524)
        if (count($obj->elements) === 1 && empty($obj->elements[0]->elements)) {
            return $this->last->parent;
        } // up to ListElement

        // Move elements
        $keys = array_keys($obj->elements);
        foreach ($keys as $key) {
            parent::insert($obj->elements[$key]);
        }

        return $this->last;
    }

    public function __toString()
    {
        return $this->wrap(parent::__toString(), $this->tag, [], false);
    }
}
