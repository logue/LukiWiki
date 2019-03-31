<?php
/**
 * InterWikiの種類列挙型.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Enums;

use BenSampo\Enum\Enum;

final class InterWikiType extends Enum
{
    const InterWikiName = 0;
    const AutoAliasName = 1;
    const Glossary = 2;
}
