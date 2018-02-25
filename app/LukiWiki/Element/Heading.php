<?php
/**
 * 見出しクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\Rule\HeadingAnchor;

/**
 * * Heading1
 * ** Heading2
 * *** Heading3.
 */
class Heading extends Element
{
    protected $level;
    protected $id;
    protected $msg_top;
    protected $text;

    public function __construct(&$root, $text)
    {
        parent::__construct();

        $this->text = $text;
        $this->level = min(3, strspn($text, '*'));
        list($text, $this->msg_top, $this->id) = $root->getAnchor($text, $this->level);
        $this->insert(ElementFactory::factory('InlineElement', null, $text));
        ++$this->level; // h2,h3,h4
    }

    public function insert(&$obj)
    {
        parent::insert($obj);

        return $this->last = &$this;
    }

    public function canContain(&$obj)
    {
        return false;
    }

    public function toString()
    {
        list($this->text, $fixed_anchor) = HeadingAnchor::get($this->text, false);
        $id = (empty($fixed_anchor)) ? $this->id : $fixed_anchor;

        return $this->msg_top.$this->wrap(parent::toString(),
            'h'.$this->level, ' id="'.$id.'"');
    }
}
