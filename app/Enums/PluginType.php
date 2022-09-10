<?php

/**
 * プラグインの種類列挙型.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Enums;

enum PluginType: int
{
    case Inline = 0;

    case Block = 1;

    case Api = 2;
}
