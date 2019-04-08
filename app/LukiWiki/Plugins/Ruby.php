<?php
/**
 * ルビプラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;

class Ruby extends AbstractPlugin implements InlinePluginInterface
{
    public function inline(): string
    {
        return '<ruby>'.e($this->params[0]).'<rt>'.e($this->body).'</rt></ruby>';
    }
}
