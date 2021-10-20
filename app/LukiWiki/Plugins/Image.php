<?php

/**
 * インライン画像プラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2021 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;

class Image extends AbstractPlugin implements InlinePluginInterface
{
    public function inline(): string
    {
        $alt = e($this->params[0]);
        if (strpos($this->params[0], 'http') === false) {
            $src = url($this->page . ':attachments/' . $this->params[0]);
        } else {
            $src = $this->params[0];
        }
        return '<img src="' . $src . '" alt="' . $alt . '" loading="lazy" />';
    }
}
