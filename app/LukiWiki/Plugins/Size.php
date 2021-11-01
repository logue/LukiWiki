<?php

/**
 * Sizeプラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2021 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;

class Size extends AbstractPlugin implements InlinePluginInterface
{
    public function inline(): string
    {
        return '<span style="font-size: ' . e($this->params[0]) . 'rem">' . e($this->body) . '</span>';
    }
}
