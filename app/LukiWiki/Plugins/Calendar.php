<?php

/**
 * カレンダープラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2021 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\BlockPluginInterface;

class Calendar extends AbstractPlugin implements BlockPluginInterface
{
    public function block(): string
    {
        return '<lw-calendar page="'.$this->page.'"></lw-calendar>';
    }
}
