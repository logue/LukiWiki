<?php
/**
 * フットノート変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

// Footnotes
class Note extends Inline
{
    /**
     * title属性に入れる説明文の文字数.
     */
    const FOOTNOTE_TITLE_MAX = 16;
    /**
     * 説明文のID.
     */
    private static $note_id = 0;

    /**
     * コンストラクタ
     */
    public function __construct(int $start)
    {
        parent::__construct($start);
    }

    /**
     * マッチパターン.
     */
    public function getPattern()
    {
        return
            '\(\('.
             '((?>(?=\(\()(?R)|(?!\)\)).)*)'.	// (1) note body
            '\)\)';
    }

    /**
     * 要素の数.
     */
    public function getCount()
    {
        return 1;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list(, $body) = $this->splice($arr);
        $note = InlineFactory::factory($body);

        $id = self::$note_id;

        // Footnote
        $this->meta['note'] = trim($note);
        // A hyperlink, content-body to footnote
        $name = '<sup><a id="notetext_'.$id.'" href="#notefoot_'.$id.'" class="note-anchor"><i class="fas fa-thumbtack fa-xs"></i> '.$id.'</a></sup>';
        ++self::$note_id;

        return parent::setParam($page, $name, $body);
    }

    public function __toString()
    {
        return $this->name;
    }
}
