<?php
/**
 * 略語要素プラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;

class Abbr extends AbstractPlugin implements InlinePluginInterface
{
    public function inline(): string
    {
        return '<abbr title="'.e($this->body).'" v-b-tooltip>'.e($this->params[0]).'</abbr>';
    }
}
