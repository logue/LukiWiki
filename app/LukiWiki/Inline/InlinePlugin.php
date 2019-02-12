<?php
/**
 * インライン型プラグイン変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use App\LukiWiki\Rules\InlineRules;

// Inline plugins
class InlinePlugin extends AbstractInline
{
    protected $plugin;
    protected $param;

    public function getPattern():string
    {
        return
            '(?:&'.
                '(?:'.
                    '(\w+)'.                        // [1] plugin name
                    '(?:'.
                        '\('.
                            '((?:(?!\)[;{]).)*)'.   // [2] parameter
                        '\)'.
                    ')?'.
                ')'.
                '(?:'.
                    '\{'.
                        '((?:(?R)|(?!};).)*)'.      // [3] body
                    '\}'.
                ')?'.
            ';)';
    }

    public function getCount():int
    {
        return 4;
    }

    public function setPattern(array $arr):void
    {
        list($this->plugin, $this->param, $this->body) = $this->splice($arr);
    }

    public function __toString()
    {
        //$body = (empty($this->body)) ? null : InlineFactory::factory($this->body);
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
        //return InlineRules::replace('&'.$this->plain.$body);
        return '<span class="badge badge-pill badge-primary" title="Plugin">&amp;'.$this->plugin.'(<var>'.$this->param.'</var>)'.'</span>';
    }
}
