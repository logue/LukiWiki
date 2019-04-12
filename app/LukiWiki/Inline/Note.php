<?php
/**
 * フットノート変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;

// Footnotes
class Note extends AbstractInline
{
    protected $count = 1;
    /**
     * 説明文のID.
     */
    private static $note_id = 0;

    public function __toString()
    {
        $id = self::$note_id;
        self::$note_id++;
        $converter = new InlineConverter([], [__CLASS__], $this->page);
        $this->meta['note'] = trim($converter->convert($this->body));

        return '<sup><a id="note-anchor-'.$id.'" href="#note-'.$id.'" class="note-anchor"><font-awesome-icon fas icon="thumbtack" size="xs">*</font-awesome-icon>'.$id.'</a></sup>';
    }

    /**
     * マッチパターン.
     */
    public function getPattern(): string
    {
        return
            '\(\('.
             '((?>(?=\(\()(?R)|(?!\)\)).)*)'.	// (1) note body
            '\)\)';
    }

    public function setPattern(array $arr): void
    {
        list($this->body) = $this->splice($arr);
    }
}
