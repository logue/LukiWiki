<?php
/**
 * 自動リンククラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;

// AutoLinks
class AutoLink extends AbstractInline
{
    public function getPattern()
    {
        return '';
    }

    public function getCount()
    {
        return 1;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list($name) = $this->splice($arr);

        return parent::setParam($page, $name, null, $name);
    }

    public function __toString()
    {
        return parent::setAutoLink($this->name, $this->alias, null, $this->page, true);
    }
}
