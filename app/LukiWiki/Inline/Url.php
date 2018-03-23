<?php
/**
 * URL変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\Rules\InlineRules;

// URLs
class Url extends Inline
{
    public function getPattern()
    {
        $s1 = $this->start + 1;

        return
            '(\[\['.                // (1) open bracket
             '((?:(?!\]\]).)+)'.    // (2) alias
             '(?:>|:)'.
            ')?'.
            '('.                    // (3) scheme
            '(?:(?:https?|ftp|ssh|git|ssh):\/\/|mailto:)'.
            //'(?:'.InlineRules::getProtocolPattern().':\/\/|mailto:)'.
            ')'.
            '([\w.-]+@)?'.          // (4) mailto name
            '([^\/"<>\s]+|\/)'.     // (5) host
            '('.                    // (6) URI
             '[\w\/\@\$()!?&%#:;.,~\'=*+-]*'.
            ')'.
            '(?('.$s1.')\]\])'; // close bracket
    }

    public function getCount()
    {
        return 6;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list(, $bracket, $alias, $scheme, $mail, $host, $uri) = $this->splice($arr);
        $this->has_bracket = substr($bracket, 0, 2) === '[[';
        if (extension_loaded('intl') && $host !== '/' && preg_match('/[^A-Za-z0-9.-]/', $host)) {
            $host = idn_to_ascii($host);
        }

        $name = $scheme.$mail.$host.$uri;

        /*
        // https?:/// -> $this->cont['ROOT_URL']
        //$name = preg_replace('#^(?:site:|https?:/)//#', ROOT_URI, $name).$uri;
        if (!$alias) {
            // Punycode化されたドメインかを判別
            $alias = (extension_loaded('intl') && strtolower(substr($host, 0, 4)) === 'xn--') ?
                ($scheme.$mail.idn_to_utf8($host).$uri)
                : $name;
            if (strpos($alias, '%') !== false) {
                // TODO:mb_convert_encoding(): Unable to detect character encodingが出るので@を付加
                $alias = mb_convert_encoding(rawurldecode($alias), 'UTF-8', 'Auto');
            }
        }
        */
        return parent::setParam($page, $name, $name);
    }

    public function __toString()
    {
        return parent::setLink($this->alias, $this->name, $this->name, 'nofollow');
    }
}
