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
    const INTERWIKINAME_PATTERN = '(\[\[)?((?:(?!\s|:|\]\]).)+):(.+)(?(1)\]\])';
    /**
     * WikiNameのマッチパターン.
     */
    const WIKINAME_PATTERN = '(?:[A-Z][a-z]+){2,}(?!\w)';
    // \c3\9f through \c3\bf correspond to \df through \ff in ISO8859-1
    //const WIKINAME_PATTERN = '(?:[A-Z](?:[a-z]|\xc3[\x9f-\xbf])+(?:[A-Z](?:[a-z]|\xc3[\x9f-\xbf])+)+)(?!\w)';
    /**
     * BracketNameのマッチパターン.
     */
    const BRACKETNAME_PATTERN = '(?!\s):?[^\r\n\t\f\[\]<>#&":]+:?(?<!\s)';
    /**
     * 注釈のパターン.
     */
    const NOTE_PATTERN = '\(\(((?:(?>(?:(?!\(\()(?!\)\)(?:[^\)]|$)).)+)|(?R))*)\)\)';
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

        // PukiWiki Adv.標準書式
        '/COLOR\(([^\(\)]*)\){([^}]*)}/'                      => '<span style="color:$1">$2</span>',
        '/SIZE\(([^\(\)]*)\){([^}]*)}/'                       => '<span style="font-size:calc($1 * .1rem);">$2</span>',
        '/COLOR\(([^\(\)]*)\):((?:(?!COLOR\([^\)]+\)\:).)*)/' => '<span style="color:$1">$2</span>',
        '/SIZE\(([^\(\)]*)\):((?:(?!SIZE\([^\)]+\)\:).)*)/'   => '<span class="h$1">$2</span>',
        '/SUP{([^}]*)}/'                                      => '<sup>$1</sup>',
        '/SUB{([^}]*)}/'                                      => '<sub>$1</sub>',
        '/LANG\(([^\(\)]*)\):((?:(?!LANG\([^\)]+\)\:).)*)/'   => '<bdi lang="$1">$2</bdi>',
        '/LANG\(([^\(\)]*)\){([^}]*)}/'                       => '<bdi lang="$1">$2</bdi>',
        '/ABBR\(([^\(\)]*)\):((?:(?!ABBR\([^\)]+\)\:).)*)/'   => '<abbr title="$1">$2</abbr>',
        '/ABBR\(([^\(\)]*)\){([^}]*)}/'                       => '<abbr title="$1">$2</abbr>',
        '/%%%(?!%)((?:(?!%%%).)*)%%%/i'                       => '<ins>$1</ins>',
        '/%%(?!%)((?:(?!%%).)*)%%/'                           => '<del>$1</del>',
        '/@@@(?!@)((?:(?!@@@).)*)@@@/'                        => '<q>$1</q>',
        '/@@(?!@)((?:(?!@@).)*)@@/'                           => '<code>$1</code>',
        '/___(?!_)((?:(?!___).)*)___/'                        => '<s>$1</s>',
        '/__(?!_)((?:(?!__).)*)__/'                           => '<u>$1</u>',
        "/'''(?!')((?:(?!''').)*)'''/"                        => '<em>$1</em>',
        "/''(?!')((?:(?!'').)*)''/"                           => '<strong>$1</strong>',
        // Markdown互換書式
        '/\*(?!\*)((?:(?!\*).)*)\*/'                          => '<em>$1</em>',
        '/\*\*(?!\*\*)((?:(?!\*\*).)*)\*\*/'                  => '<strong>$1</strong>',
        '/`(?!`)((?:(?!`).)*)`/'                              => '<code>$1</code>',
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
        $pattern = array_keys(self::$rules);
        $replace = array_values(self::$rules);

        return preg_replace($pattern, $replace, $str);
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
