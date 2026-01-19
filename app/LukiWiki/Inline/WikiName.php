<?php

/**
 * Wiki名クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use App\LukiWiki\Rules\InlineRules;

// WikiNames
class WikiName extends AbstractInline
{
    protected $count = 1;

    public function __toString()
    {
        return parent::setAutoLink(
            $this->name,
            $this->alias,
            null,
            $this->page
        );
    }

    public function getPattern(): string
    {
        return InlineRules::WIKINAME_PATTERN;
    }

    public function setPattern(array $arr, ?string $page = null): void
    {
        $this->name = $this->splice($arr)[0];
        $this->anchor = $this->name;
    }
}
