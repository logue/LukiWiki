<?php

/**
 * 回り込み解除プラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2021 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;

class Clearfix extends AbstractPlugin implements InlinePluginInterface
{
    public function block(): string
    {
        return '<div class="clearfix">'. $this->body . '</div>';
    }
}
