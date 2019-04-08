<?php
/**
 * ブロック型プラグインクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\Enums\PluginType;
use App\LukiWiki\AbstractElement;
use Config;

/**
 *  Block plugin: @something (started with '@').
 */
class BlockPlugin extends AbstractElement
{
    protected $name;
    protected $params;
    protected $body;

    public function __construct($out, $page)
    {
        parent::__construct();
        $this->page = $page;
        $this->name = $out[0];
        $this->params = explode(',', $out[1]);
        $this->body = $out[2];
    }

    public function __toString()
    {
        if (Config::has('lukiwiki.plugin.'.$this->name)) {
            $class = Config::get('lukiwiki.plugin.'.$this->name);
            $plugin = new $class(PluginType::Block, $this->params, $this->body, $this->page);

            return $plugin;
        }

        // TODO:Call @plugin
        $ret = [
            '<div class="card">',
            '<div class="card-header">@'.$this->name.'</div>',
        ];
        if (\count($this->params) !== 0 && $this->body) {
            $ret[] = '<div class="card-body">';
            if (\count($this->params) !== 0) {
                $ret[] = '<p class="card-title"><code>'.parent::processText(implode(' ,', $this->params)).'</code></p>';
            }
            if ($this->body) {
                $ret[] = '<p class="card-text"><pre class="pre">'.parent::processText($this->body).'</pre></p>';
            }
            $ret[] = '</div>';
        }
        $ret[] = '</div>';

        return implode("\n", $ret);
    }
}
