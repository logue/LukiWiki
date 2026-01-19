<?php

/**
 * インライン型プラグイン変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\Enums\PluginType;
use App\LukiWiki\AbstractInline;
use Config;

// Inline plugins
class InlinePlugin extends AbstractInline
{
    protected $name;

    protected $params;

    protected $count = 3;

    public function __toString()
    {
        if (Config::has('lukiwiki.plugin.'.$this->name)) {
            $class = Config::get('lukiwiki.plugin.'.$this->name);
            $plugin = new $class(PluginType::Inline, $this->params, $this->body, $this->page);

            return $plugin;
        }
        $body = (empty($this->body)) ? null : InlineFactory::factory($this->body, $this->page);

        return '<span class="badge badge-pill badge-primary" title="Plugin">&amp;'.
            $this->name.
            (\count($this->params) < 1 ? '(<var>'.implode(',', $this->params).'</var>)' : '').
            (! empty($body) ? '{'.$body.'}' : '').';</span>';
    }

    public function getPattern(): string
    {
        return
            '(?:\&amp;'.
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

    public function setPattern(array $arr): void
    {
        // dd($this->getPattern(), $arr, $this->splice($arr));
        [$name, $param, $this->body] = $this->splice($arr);
        $this->name = strtolower($name);
        $this->params = explode(',', $param);
    }
}
