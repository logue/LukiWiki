<?php
/**
 * ブロック型プラグインクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 *  Block plugin: #something (started with '#').
 */
class BlockPlugin extends Element
{
    protected $name;
    protected $param;

    public function __construct($out)
    {
        parent::__construct();
        list(, $this->name, $this->param) = array_pad($out, 3, null);
    }

    public function canContain(&$obj)
    {
        return false;
    }

    public function toString()
    {
        // Call #plugin
        return '<div class="card text-white bg-primary"><div class="card-body">Block Plugin</div></div>';
        //return PluginRenderer::executePluginBlock($this->name, $this->param);
    }
}
