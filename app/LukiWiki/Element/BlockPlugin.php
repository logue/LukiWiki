<?php
/**
 * ブロック型プラグインクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;

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
        //echo join(",", $out);
        $this->page = $page;
        $this->name = $out[0];
        $params = explode(',', $out[1]);
        $this->body = str_replace("\r", "\n", array_pop($params));
        $this->params = $params;
    }

    public function __toString()
    {
        // TODO:Call @plugin
        $ret = [
            '<div class="card">',
            '<div class="card-header">@'.$this->name.'</div>',
        ];
        if (count($this->params) !== 0 && $this->body) {
            $ret[] = '<div class="card-body">';
            if (count($this->params) !== 0) {
                $ret[] = '<h5 class="card-title">'.htmlspecialchars(implode(', ', $this->params)).'</h5>';
            }
            if ($this->body) {
                $ret[] = '<p class="card-text">'.nl2br(htmlspecialchars($this->body)).'</p>';
            }
            $ret[] = '</div>';
        }
        $ret[] = '</div>';

        return implode("\n", $ret);
    }
}
