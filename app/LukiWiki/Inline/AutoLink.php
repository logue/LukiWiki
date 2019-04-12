<?php
/**
 * 自動リンククラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018,2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use App\Models\Page;

// AutoLinks
class AutoLink extends AbstractInline
{
    protected $count = 2;

    public function __toString()
    {
        return '<a href="'.url($this->name).'" class="autolink">'.$this->name.'</a>';
    }

    public function getPattern(): string
    {
        return Page::getTrie();
    }

    public function setPattern(array $arr, string $page = null): void
    {
        $this->name = $arr[0];
    }
}
