<?php

/**
 * Colorプラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;

class Color extends AbstractPlugin implements InlinePluginInterface
{
    const COLOR_MATCH_PATTERN = '/^(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z-]+)$/i';

    private $style = [];

    public function inline(): string
    {
        $style = [];

        list($style['color'], $style['background-color']) = array_pad($this->params, 2, '');

        foreach ($style as $key => $value) {
            if (!empty($value) && !preg_match(self::COLOR_MATCH_PATTERN, $value)) {
                return $this->error('Invalid color: ' . e($value));
            }
            $this->style[] = $key . ':' . $value;
        }

        return '<span style="' . e(implode(';', $this->style)) . '">' . e($this->body) . '</span>';
    }
}
