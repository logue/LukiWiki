<?php
/**
 * ブラケット名クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use App\LukiWiki\Rules\InlineRules;
use App\Models\Page;

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
                    '('.
                        '(?:https?|ftp|ssh)'.   // protocol
                        '(?::\/\/[^\(\)][-_.!~*\'a-zA-Z0-9;\/?:\@&=+\$,%#]+)'.
                        '|'.
                        '(?:.[^\r\n\t\f&"(\)]+?)'.
                        '(?:\#(\w[^\#]+?))?'.       // [3] Anchor
                    ')'.
                    '(?:\s+(?:"(.*[^\(\)"]?)"))?'.  // [4] Title
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

    public function setPattern(array $arr)
    {
        list($this->alias, $this->href, $this->anchor, $this->title, $this->body) = $this->splice($arr);

        //dd($this);

        if (strpos($this->alias, '#')) {
            $this->anchor = $this->alias;
        }

        if (empty($this->href)) {
            $this->href = $this->alias;
        }

        if (strpos($this->href, 'http') === false) {
            $this->page = parent::getPageName($this->href);
        }

        //dd($this->page);

        if (!empty($page)) {
            if (empty($this->anchor)) {
                //return false;
            } elseif (!InlineRules::isWikiName($page) && empty($alias)) {
                $this->alias = $this->href.$this->anchor;
            }
        }

        //self::setParam(['page'=>$page, 'href'=>url(parent::getPageName($page)), 'alias' => $alias, 'title'=> $title, 'option' => $option]);
    }

    public function __toString()
    {
        //dd($this->href);
        if (strpos($this->href, 'http') !== false) {
            return '<a href="'.$this->href.'" title="'.$this->title.'">'.$this->alias.'</a>';
        }

        if (!empty($this->page)) {
            // 自動リンク
            if (in_array($this->page, array_keys(Page::getEntries()))) {
                return '<a href="'.url($this->page).'" title="'.$this->title.'">'.$this->alias.'</a>';
            }
            // ページが見つからない場合のリンク
            return '<span class="bg-light text-dark">'.$this->alias.'<a href="'.url($this->page).':edit" rel="nofollow" title="Edit '.$this->page.'" v-b-tooltip>?</a></span>';
        }
    }
}
