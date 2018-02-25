<?php
/**
 * ブラケット名クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\Rules\InlineRules;

class BracketName extends Inline
{
    protected $anchor;
    protected $refer;

    public function __construct($start)
    {
        parent::__construct($start);
    }

    public function getPattern()
    {
        $s2 = $this->start + 2;
        // [[ (1) > (3) # (4) ]]
        // [[ (2) ]]
        return
            '\[\['.                     // Open bracket [[
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
    }

    public function getCount()
    {
        return 4;
    }

    public function setPattern($arr, $page)
    {
        list(, $alias, , $name, $this->anchor) = $this->splice($arr);
        if (empty($name) && empty($this->anchor)) {
            return false;
        }
        /*
                if (empty($name) || !Utility::isWikiName($name)) {
                    if (empty($alias)) {
                        $alias = $name.$this->anchor;
                    }
                    if (!empty($name)) {
                        $name = self::getFullname($name, $page);
                        if (!empty($name) && !Factory::Wiki($name)->isValied()) {
                            return false;
                        }
                    }
                }
                */

        return parent::setParam($page, $name, null, 'pagename', $alias);
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
