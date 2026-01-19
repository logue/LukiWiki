<?php

/**
 * キャプションクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;

/**
 * Caption.
 */
class TableCaption extends AbstractElement
{
    /**
     * コンストラクタ
     */
    public function __construct(string $text, string $page)
    {
        parent::__construct();

        $obj = new InlineElement($text, $page);
        $this->meta = $obj->getMeta();
        $this->insert($obj);
    }

    public function __toString()
    {
        return $this->wrap(parent::__toString(), 'caption', [], false);
    }

    public function canContain($obj)
    {
        return $obj instanceof Table;
    }
}
