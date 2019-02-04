<?php
/**
 * ブラケット名クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use App\LukiWiki\Rules\InlineRules;

class BracketName extends AbstractInline
{
    protected $anchor;
    protected $refer;

    public function getPattern()
    {
        $s2 = $this->start + 2;
        // [alt](WikiName "title"){option}
        return
            '(?:\['.
                '(.[^\]\[]+)'.                          // [1] alias
            '\])'.
            '(?:'.
                '\('.
                   '(.[^\r\n\t\f\[\]<>#&":\(\)]+?)'.    // [2] Name
                   '(?:\#(\w[^\#]+?))?'.                // [3] Anchor
                   '(?:\s+(?:"(.*[^\(\)\[\]"]?)"))?'.   // [4] Title
                '\)'.
            ')?'.
            '(?:\{'.
                '(.*[^\}]?)'.                       // [5] Body (option)
            '\})?';
    }

    public function getCount()
    {
        return 5;
    }

    public function setPattern(array $arr, string $page = null)
    {
        //dd($this->getPattern(), $arr,  $this->splice($arr));

        list($this->alias, $this->href, $this->anchor, $this->title, $this->body) = $this->splice($arr);

        if (empty($this->href)) {
            $this->href = $this->anchor;
        }
        if (strpos($this->alias, '#')) {
            $this->anchor = $alias;
        }
        /*
        if (empty($page)) {


            if (empty($this->anchor)) {
                //return false;
            } elseif (!InlineRules::isWikiName($page) && empty($alias)) {
                $alias = $name.$this->anchor;
            }

        }
        */

        //self::setParam(['page'=>$page, 'href'=>url($page), 'alias' => $alias, 'title'=> $title, 'option' => $option]);
    }

    public function __toString()
    {
        return '<a href="'.$this->alias.'" title="'.$this->title.'">'.$this->alias.'</a>';
    }
}
