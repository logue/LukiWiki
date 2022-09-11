<?php

/**
 * プラグインの種類列挙型.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PluginType extends Enum
{
    const Inline = 0;

    const Block = 1;

    const Api = 2;
}
