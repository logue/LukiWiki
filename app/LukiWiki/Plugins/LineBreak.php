<?php

/**
 * 改行プラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;

class LineBreak extends AbstractPlugin implements InlinePluginInterface
{
    public function inline(): string
    {
        return '<br />';
    }
}
