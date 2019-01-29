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
        // [[ (1) > (3) # (4) ]]
        // [[ (2) ]]
        /*
        return
            '\['.                     // Open bracket [[
            '(?:((?:(?!\]\]).)+)>)?'.   // (1) Alias >
            '(\[\[)?'.                  // (2) Open bracket
            '('.                        // (3) PageName
             '(?:'.InlineRules::WIKINAME_PATTERN.')'.
             '|'.
             '(?:'.InlineRules::BRACKETNAME_PATTERN.')'.
            ')?'.
            '(\#(?:[A-Za-z0-9][\w-]*)?)?'. // (4) Anchor
            '(?('.$s2.')\]\])'.     // Close bracket if (2)
            '\]\]';                     // Close bracket ]]
        */
        // [page]
        // [alias](page)
        return
            '\['.   // blacket open
                '((?:(?!\]).)+)'. // [1] Link Text
            '\]'.
            '(?:\('.
                '('.    // [2] Link to
                    '(?:'.InlineRules::WIKINAME_PATTERN.')'.
                    '|'.
                    '(?:'.InlineRules::BRACKETNAME_PATTERN.')'.
                ')'.    // [2] Link to end
                '((?:\#).+)?'. // [3] Anchor
                '(?:\s{1,}?"'.
                    '((?:(?!"\)).)+)'. // [4] title
                '")?'.
            '\))?';
    }

    public function getCount()
    {
        return 4;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list(, $alias, , $name, $this->anchor) = $this->splice($arr);
        //dd($name);
        if (empty($name)) {
            if (strpos($alias, '#')) {
                $this->anchor = $alias = $name;
            }
            if (empty($this->anchor)) {
                return false;
            } elseif (!InlineRules::isWikiName($name) && empty($alias)) {
                $alias = $name.$this->anchor;
            }
//        } elseif (!isset($this->pages->$name)) {
//            return false;
        }

        return parent::setParam($page, $name, '', $alias);
    }

    public function __toString()
    {
        return parent::setAutoLink(
            $this->name,
            $this->alias,
            $this->anchor,
            $this->page
        );
    }
}
