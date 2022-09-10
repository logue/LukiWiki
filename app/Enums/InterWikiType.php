<?php

/**
 * InterWikiの種類列挙型.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Enums;

enum InterWikiType: int
{
    case InterWikiName = 0;

    case AutoAliasName = 1;

    case Glossary = 2;
}
