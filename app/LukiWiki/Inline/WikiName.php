<?php
/**
 * Wiki名クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\Rules\InlineRules;

// WikiNames
class WikiName extends Inline
{
    public function __construct($start)
    {
        parent::__construct($start);
    }

    public function getPattern()
    {
        return InlineRules::WIKINAME_PATTERN;
    }

    public function getCount()
    {
        return 1;
    }

    public function setPattern(array $arr, string $page = null)
    {
        $name = $this->splice($arr)[0];

        return parent::setParam($page, $name, null, $name);
    }

    public function __toString()
    {
        return parent::setAutoLink(
            $this->name,
            $this->alias,
            null,
            $this->page
        );
    }
}
