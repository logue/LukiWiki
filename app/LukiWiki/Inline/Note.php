<?php
/**
 * フットノート変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\Rules\InlineRules;

// Footnotes
class Note extends Inline
{
    protected $notes = [];
    /**
     * title属性に入れる説明文の文字数.
     */
    const FOOTNOTE_TITLE_MAX = 16;
    /**
     * 説明文のリンクを相対パスにする。（ページ内リンクのみにする）.
     */
    const ALLOW_RELATIVE_FOOTNOTE_ANCHOR = true;
    /**
     * 説明文のID.
     */
    private static $note_id = 0;

    /**
     * コンストラクタ
     */
    public function __construct($start)
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

    public function setPattern($arr, $page)
    {
        list(, $body) = $this->splice($arr);

        // Recover of notes(miko)
        if (count($this->notes) === 0) {
            self::$note_id = 0;
        }

        $id = ++self::$note_id;
        $note = InlineFactory::factory($body);
        $page = isset($vars['page']) ? rawurlencode($vars['page']) : null;

        // Footnote
        $this->notes[$id] =
            '<li id="notefoot_'.$id.'">'.
            '<a href="#notetext_'.$id.'">'.InlineRules::FOOTNOTE_ANCHOR_ICON.$id.'</a>'.$note.
            '</li>';

        // A hyperlink, content-body to footnote
        $name = '<a id="notetext_'.$id.'" href="#notefoot_'.$id.'" class="note-anchor">'.InlineRules::FOOTNOTE_ANCHOR_ICON.$id.'</a>';

        return parent::setParam($page, $name, $body);
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getExplain()
    {
        return '<ul class="notes">'."\n".implode("\n", $this->notes).'</ul>'."\n";
    }
}
