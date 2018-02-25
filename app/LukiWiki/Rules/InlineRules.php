<?php
/**
 * インラインWiki文法定義クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Rules;

class InlineRules
{
    /**
     * InterWikiNameのマッチパターン.
     */
    const INTERWIKINAME_PATTERN = '(\[\[)?((?:(?!\s|:|\]\]).)+):(.+)(?(1)\]\])';
    /**
     * WikiNameのマッチパターン.
     */
    //const WIKINAME_PATTERN ='(?:[A-Z][a-z]+){2,}(?!\w)';
    // \c3\9f through \c3\bf correspond to \df through \ff in ISO8859-1
    const WIKINAME_PATTERN = '(?:[A-Z](?:[a-z]|\\xc3[\\x9f-\\xbf])+(?:[A-Z](?:[a-z]|\\xc3[\\x9f-\\xbf])+)+)(?!\w)';
    /**
     * BracketNameのマッチパターン.
     */
    const BRACKETNAME_PATTERN = '(?!\s):?[^\r\n\t\f\[\]<>#&":]+:?(?<!\s)';
    /**
     * 注釈のパターン.
     */
    const NOTE_PATTERN = '\(\(((?:(?>(?:(?!\(\()(?!\)\)(?:[^\)]|$)).)+)|(?R))*)\)\)';
    /**
     * 内部リンクのアイコン.
     */
    const INTERNAL_LINK_ICON = '<i class="fa fa-external-link-square" title="Internal Link" aria-hidden="true"></i>';
    /**
     * 外部リンクのアイコン.
     */
    const EXTERNAL_LINK_ICON = '<i class="fa fa-external-link" title="External Link" aria-hidden="true"></i>';
    /**
     * メールリンクのアイコン.
     */
    const MAILTO_ICON = '<i class="fa fa-envelope" title="mailto:" aria-hidden="true"></i>';
    /**
     * 電話番号リンクのアイコン.
     */
    const TELEPHONE_ICON = '<i class="fa fa-phone" title="tel:" aria-hidden="true"></i>';
    /**
     * InterWikiNameのアイコン.
     */
    const INTERWIKINAME_ICON = '<i class="fa fa-globe" title="InterWikiName" aria-hidden="true"></i>';
    /**
     * 部分編集リンクのアイコン.
     */
    const PARTIAL_EDIT_LINK_ICON = '<i class="fa fa-pencil" title="Edit here" aria-hidden="true"></i>';
    /**
     * 見つからないページのリンク.
     */
    const NOEXISTS_STRING = '<span class="noexists">%s</span>';
    /**
     * imgタグに展開する拡張子のパターン.
     */
    const IMAGE_EXTENTION_PATTERN = '/\.(gif|png|bmp|jpe?g|svg?z|webp|ico)$/i';
    /**
     * audioタグで展開する拡張子のパターン.
     */
    const AUDIO_EXTENTION_PATTERN = '/\.(mp3|ogg|m4a)$/i';
    /**
     * videoタグで展開する拡張子のパターン.
     */
    const VIDEO_EXTENTION_PATTERN = '/\.(mp4|webm)$/i';
    /**
     * ノートのアイコン.
     */
    const FOOTNOTE_ANCHOR_ICON = '<i class="fa fa-thumb-tack" aria-hidden="true"></i>';
    /**
     * デフォルトのテキストルール.
     */
    private static $default_rules = [
        // 実体参照パターンおよびシステムで使用するパターンを$line_rulesに加える
        // XHTML5では&lt;、&gt;、&amp;、&quot;と、&apos;のみ使える。
        // http://www.w3.org/TR/html5/the-xhtml-syntax.html
        '&amp;(#[0-9]+|#x[0-9a-f]+|(?=[a-zA-Z0-9]{2,8})(?:apos|amp|lt|gt|quot));' => '&$1;',
        // 行末にチルダは改行
        "\r" => "<br />\n",
        // PukiWiki Adv.標準書式
        'COLOR\(([^\(\)]*)\){([^}]*)}'                      => '<span style="color:$1">$2</span>',
        'SIZE\(([^\(\)]*)\){([^}]*)}'                       => '<span style="font-size:$1px">$2</span>',
        'COLOR\(([^\(\)]*)\):((?:(?!COLOR\([^\)]+\)\:).)*)' => '<span style="color:$1">$2</span>',
        'SIZE\(([^\(\)]*)\):((?:(?!SIZE\([^\)]+\)\:).)*)'   => '<span class="size$1">$2</span>',
        'SUP{([^}]*)}'                                      => '<sup>$1</sup>',
        'SUB{([^}]*)}'                                      => '<sub>$1</sub>',
        'LANG\(([^\(\)]*)\):((?:(?!LANG\([^\)]+\)\:).)*)'   => '<bdi lang="$1">$2</bdi>',
        'LANG\(([^\(\)]*)\){([^}]*)}'                       => '<bdi lang="$1">$2</bdi>',
        '%%%(?!%)((?:(?!%%%).)*)%%%'                        => '<ins>$1</ins>',
        '%%(?!%)((?:(?!%%).)*)%%'                           => '<del>$1</del>',
        '@@@(?!@)((?:(?!@@).)*)@@@'                         => '<q>$1</q>',
        '@@(?!@)((?:(?!@@).)*)@@'                           => '<code>$1</code>',
        '___(?!@)((?:(?!@@).)*)___'                         => '<s>$1</s>',
        '__(?!@)((?:(?!@@).)*)__'                           => '<u>$1</u>',
        // htmlspecialchars関数対策。'を&#39;に変えてしまうため。
        '&#039;&#039;&#039;(?!&#039;)((?:(?!&#039;&#039;&#039;).)*)&#039;&#039;&#039;' => '<em>$1</em>',
        '&#039;&#039;(?!&#039;)((?:(?!&#039;&#039;).)*)&#039;&#039;'                   => '<strong>$1</strong>',
    ];

    public static function Rule()
    {
        return self::$default_rules;
    }

    /**
     * InterWikiNameかをチェック.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isInterWiki($str)
    {
        return preg_match('/^'.self::INTERWIKINAME_PATTERN.'$/', $str);
    }

    /**
     * ブラケット名か.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isBracketName($str)
    {
        return preg_match('/^(?!\/)'.self::BRACKETNAME_PATTERN.'$(?<!\/$)/', $str);
    }

    /**
     * Wiki名か.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isWikiName($str)
    {
        return preg_match('/^'.self::WIKINAME_PATTERN.'$/', $str);
    }

    /**
     * ブラケット（[[ ]]）を取り除く.
     *
     * @param string $str
     *
     * @return string
     */
    public static function stripBracket($str)
    {
        $match = [];

        return preg_match('/^\[\[(.*)\]\]$/', $str, $match) ? $match[1] : $str;
    }
}
