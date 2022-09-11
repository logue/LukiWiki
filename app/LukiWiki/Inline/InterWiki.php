<?php

/**
 * InterWiki変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013,2014,2018,2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;

/**
 * URLs (InterWiki definition on "InterWikiName").
 */
class InterWiki extends AbstractInline
{
    protected $count = 4;

    public function __toString()
    {
        $target = empty($this->redirect) ? $this->name : $this->redirect.rawurlencode($this->name);

        return parent::setAutoLink($this->alias, $target, $this->name);
    }

    public function getPattern(): string
    {
        // [alias](URL "title"){option}
        return
            '\['.
                '(.[^\]\[]+)'.              // [1] alias
            '\]'.
            '\('.
                '('.                        // [2] Link to
                    '[^\(\)]'.
                    '(?:https?|ftp|ssh)'.   // protocol
                    '(?::\/\/[-_.!~*\'a-zA-Z0-9;\/?:\@&=+\$,%#]+)'. // path, port, etc
                ')'.                        // [2] Name end
                '\s*("(?:.*[^"])")?\s*'.    // [3] Title
            '\)'.
            '(?:\{'.
                '(.*[^\}])'.                // [4] Body (option)
            '\})?';
    }

    public function setPattern(array $arr): void
    {
        [$this->alias, $this->href, $this->title, $this->body] = $this->splice($arr);
    }
}
