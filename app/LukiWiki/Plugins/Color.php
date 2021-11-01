<?php

/**
 * Colorプラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019,2021 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\InlinePluginInterface;

class Color extends AbstractPlugin implements InlinePluginInterface
{
    private const COLOR_MATCH_PATTERN = '/^(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z-]+)$/i';

    private $style = [];

    public function inline(): string
    {
        $s = [];

        list($s['color'], $s['background-color']) = array_pad($this->params, 2, '');

        foreach ($s as $key => $value) {
            if (!empty($value) && !preg_match(self::COLOR_MATCH_PATTERN, $value)) {
                return $this->error('Invalid color: ' . e($value));
            }
            $this->style[] = $key . ':' . $value;
        }

        return '<span style="' . e(implode(';', $this->style)) . '">' . e($this->body) . '</span>';
    }
}
