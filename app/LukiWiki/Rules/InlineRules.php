<?php

/**
 * インラインWiki文法定義クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Rules;

use RegexpTrie\RegexpTrie;

class InlineRules
{
    /**
     * InterWikiNameのマッチパターン.
     */
    public const INTERWIKINAME_PATTERN = '\[.*\]\((https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)[\t{1,}|\s{1,}]"".*""\)';
    /**
     * WikiNameのマッチパターン.
     */
    public const WIKINAME_PATTERN = '(?:[A-Z][a-z]+){2,}(?!\w)';
    // \c3\9f through \c3\bf correspond to \df through \ff in ISO8859-1
    //const WIKINAME_PATTERN = '(?:[A-Z](?:[a-z]|\xc3[\x9f-\xbf])+(?:[A-Z](?:[a-z]|\xc3[\x9f-\xbf])+)+)(?!\w)';
    /**
     * BracketNameのマッチパターン.
     */
    public const BRACKETNAME_PATTERN = '(?!\s):?[^\r\n\t\f\[\]<>#&":\(\)]+:?(?<!\s)';
    /**
     * 注釈のパターン.
     */
    public const NOTE_PATTERN = '\(\(((?:(?>(?:(?!\(\()(?!\)\)(?:[^\)]|$)).)+)|(?R))*)\)\)';
    /**
     * デフォルトのテキストルール.
     */
    private static $rules = [
        // 実体参照パターンおよびシステムで使用するパターンを$line_rulesに加える
        // XHTML5では&lt;、&gt;、&amp;、&quot;と、&apos;のみ使える。
        // http://www.w3.org/TR/html5/the-xhtml-syntax.html
        '/&amp;(#[0-9]+|#x[0-9a-f]+|(?=[a-zA-Z0-9]{2,8})(?:apos|amp|lt|gt|quot));/' => '&$1;',
        // 行末にチルダは改行
        "/\r|&amp;br;/" => '<br />',

        // LukiWiki標準書式
        '/COLOR\((.+)?\){(.+)?}/u'      => '<span style="color:$1">$2</span>',
        '/BGCOLOR\((.+)?\){(.+)?}/u'    => '<span style="background-color:$1">$2</span>',
        '/SIZE\(([\d|\.]+)?\){(.+)?}/u' => '<span style="font-size:$1rem">$2</span>',
        '/SUP\((.+)?\){(.+)?}/u'        => '<sup>$1</sup>',
        '/SUB\((.+)?\){(.+)?}/u'        => '<sub>$1</sub>',
        '/LANG\((\w\w)?\){(.+)?}/u'     => '<bdi lang="$1">$2</bdi>',
        '/ABBR\((.+)?\){(.+)?}/u'       => '<abbr title="$1">$2</abbr>',
        '/%%(?!%)((?:(?!%%).)*)%%/u'    => '<ins>$1</ins>',
        '/~~(?!%)((?:(?!~~).)*)~~/u'    => '<del>$1</del>',
        '/@@@(?!@)((?:(?!@@@).)*)@@@/u' => '<q>$1</q>',
        '/___(?!_)((?:(?!___).)*)___/u' => '<s>$1</s>',
        '/__(?!_)((?:(?!__).)*)__/u'    => '<u>$1</u>',
        "/'''(?!')((?:(?!''').)*)'''/u" => '<i>$1</i>',
        "/''(?!')((?:(?!'').)*)''/u"    => '<b>$1</b>',

        // Markdown互換書式
        '/\*(?!\*)((?:(?!\*).)*)\*/'                    => '<em>$1</em>',
        '/\*\*(?!\*\*)((?:(?!\*\*).)*)\*\*/'            => '<strong>$1</strong>',
        '/\*\*\*(?!\*\*\*)((?:(?!\*\*\*).)*)\*\*\*/'    => '<strong><em>$1</em></strong>',
        '/`(?!`)((?:(?!`).)*)`/'                        => '<code>$1</code>',
    ];

    // 自動リンク対象のURLスキーム
    private static $uri_schmes = [
        // Official
        'aaa', 'aaas', 'about', 'acap', 'cap', 'cid', 'crid', 'data', 'dav', 'dict', 'dns', 'fax', 'file', 'ftp', 'geo', 'go',
        'Gopher', 'h323', 'http', 'https', 'iax', 'im', 'imap', 'info', 'ldap', 'mailto', 'mid', 'news', 'nfs', 'nntp', 'pop',
        'rsync', 'pres', 'rtsp', 'sip', 'sips', 'snmp', 'tag', 'tel', 'telnet', 'tftp', 'urn', 'view-source', 'wais', 'ws', 'xmpp',
        // Un Official
        'afp', 'aim', 'apt', 'bolo', 'bzr', 'callto', 'coffee', 'cvs', 'daap', 'dsnp', 'ed2k', 'feed', 'fish', 'gg', 'git',
        'gizmoproject', 'irc', 'ircs', 'itms', 'javascript', 'ldaps', 'magnet', 'mms', 'msnim', 'postal2', 'secondlife', 'skype',
        'spotify', 'ssh', 'svn', 'sftp', 'smb', 'sms', 'steam', 'webcal', 'winamp', 'wyciwyg', 'xfire', 'ymsgr',
    ];

    // User-defined rules (convert without replacing source)
    public static function replace($str)
    {
        return preg_replace(array_keys(self::$rules), array_values(self::$rules), $str);
    }

    public static function getProtocolPattern()
    {
        return RegexpTrie::union(self::$uri_schmes)->build();
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
        return preg_match('/^' . self::INTERWIKINAME_PATTERN . '$/', $str);
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
        return preg_match('/^(?!\/)' . self::BRACKETNAME_PATTERN . '$(?<!\/$)/', $str);
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
        return preg_match('/^' . self::WIKINAME_PATTERN . '$/', $str);
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
