<?php
/**
 * Github Markdown Pre.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * ```lang ... ```.
 */
class GfmPre extends Element
{
    private $lang;

    public function __construct($root, $text, $lang)
    {
        parent::__construct();
        $this->lang = $lang;
        $this->elements[] = htmlspecialchars(trim($text), ENT_HTML5, 'UTF-8');
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

    public function toString()
    {
        return $this->wrap(implode("\n", $this->elements), 'pre', ['class' => 'cm', 'data-lang' => $this->lang], false);
    }
}
