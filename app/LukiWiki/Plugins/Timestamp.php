<?php

/**
 * タイムスタンププラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;
use Carbon\Carbon;

class Timestamp extends AbstractPlugin implements InlinePluginInterface
{
    public function inline(): string
    {
        return '<time>'.Carbon::createFromTimestamp($this->params[0])->toDateTimeString().'</time>';
    }
}
