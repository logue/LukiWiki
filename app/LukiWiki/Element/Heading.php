<?php
/**
 * 見出しクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;
use App\LukiWiki\Rules\HeadingAnchor;

/**
 * # Heading1
 * ## Heading2
 * ### Heading3
 * #### Heading4
 * ##### Heading5.
 */
class Heading extends AbstractElement
{
    protected $level;
    protected $id;
    protected $text;

    public function __construct($root, $text, $page)
    {
        parent::__construct();

        $this->level = min(5, strspn($text, '#'));
        $this->page = $page;
        list($text, $this->msg_top, $this->id) = $root->getAnchor($text, $this->level);
        //dd($this->id);

        $content = new InlineElement($text, $this->page);
        $this->meta = $content->getMeta();
        $this->insert($content);

        ++$this->level; // h2,h3,h4,h5,h6
    }

    public function insert($obj)
    {
        parent::insert($obj);

        return $this->last = $this;
    }

    public function __toString()
    {
        list($this->text, $fixed_anchor) = HeadingAnchor::get(parent::__toString(), false);
        $id = (empty($fixed_anchor)) ? $this->id : $fixed_anchor;

        $this->meta[$id] = $this->text;

        return $this->wrap(parent::__toString(), 'h'.$this->level, ['id' => $id], false);
    }
}
