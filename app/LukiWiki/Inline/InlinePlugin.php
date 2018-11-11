<?php
/**
 * インライン型プラグイン変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use App\LukiWiki\Rules\InlineRules;

// Inline plugins
class InlinePlugin extends AbstractInline
{
    protected $pattern;
    protected $plain;
    protected $param;

    public function getPattern()
    {
        $this->pattern =
            '&'.
             '('.        // (1) plain
              '(\w+)'.   // (2) plugin name
               '(?:'.
               '\('.
                '((?:(?!\)[;{]).)*)'. // (3) parameter
               '\)'.
              ')?'.
             ')';

        return $this->pattern.
             '(?:'.
              '\{'.
               '((?:(?R)|(?!};).)*)'. // (4) body
              '\}'.
             ')?'.
            ';';
    }

    public function getCount()
    {
        return 4;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list($all, $this->plain, $name, $this->param, $body) = $this->splice($arr);

        // Re-get true plugin name and patameters (for PHP 4.1.2)
        $matches = [];
        if (preg_match('/^'.$this->pattern.'/x', $all, $matches) && $matches[1] !== $this->plain) {
            list(, $this->plain, $name, $this->param) = $matches;
        }

        return parent::setParam($page, $name, $body);
    }

    public function __toString()
    {
        $body = (empty($this->body)) ? null : InlineFactory::factory($this->body);
        //$str = false;

        // Try to call the plugin
        // TODO
        /*
        $str = PluginRenderer::executePluginInline($this->name, $this->param, $body);

        if ($str !== false) {
            return $str; // Succeed
        } else {
            // No such plugin, or Failed
            $body = (empty($body) ? '' : '{'.$body.'}').';';


        }
        */
        return InlineRules::replace('&'.$this->plain.$body);
        //return '<span class="badge badge-pill badge-primary" title="Plugin">&amp;'.$this->name.'(<var>'.$this->param.'</var>)'.'</span>';
    }
}
