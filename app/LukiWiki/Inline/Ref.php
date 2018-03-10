<?php
/**
 * メディア埋め込み.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace LukiWiki\Renderer\Inline;

/**
 * 簡易表記 {{param|body}}
 * from XpWiki.
 */
class Ref extends Inline
{
    protected $pattern;
    protected $plain;
    protected $param;

    public function __construct($start)
    {
        parent::__construct($start);
    }

    public function getPattern()
    {
        return
            '\{\{'.
             '(.*?)'.   // (1) parameter
             '(?:\|'.
              '(.*?)'.  // (2) body (optional)
             ')?'.
            '\}\}';
    }

    public function getCount()
    {
        return 2;
    }

    public function setPattern($arr, $page)
    {
        list(, $this->param, $body) = $this->splice($arr);
        $this->param = trim($this->param);

        // TODO
    }

    public function __toString()
    {
        // TODO
    }
}
