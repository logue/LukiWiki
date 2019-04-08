<?php

namespace App\LukiWiki;

interface InlinePluginInterface
{
    public function inline(): string;
}
