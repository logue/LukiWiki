<?php
/**
 * URL変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use Symfony\Polyfill\Intl\Idn\Idn;

// URLs
class Url extends AbstractInline
{
    public function getPattern()
    {
        $s1 = $this->start + 1;

        return
            '('.
              '(?:(?:https?|ftp|ssh|git|ssh):\/\/|mailto:)'.    // [1] scheme
            ')'.
            '([\w.-]+@)?'.                                      // [2] mailto name
            '([^\/"<>\s]+|\/)'.                                 // [3] host
            '('.
              '[\w\/\@\$()!?&%#:;.,~\'=*+-]*'.                  // [4] URI
            ')';
    }

    public function getCount()
    {
        return 4;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list($scheme, $user, $host, $path) = $this->splice($arr);
        if (substr($host, 0, 4) === 'xn--') {
            $host = Idn::idn_to_ascii($host, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);
        }

        $this->href = $scheme.$user.$host.$path;

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
    }

    public function __toString()
    {
        return '<a href="'.$this->href.'" rel="nofollow external">'.$this->processText($this->href).'<font-awesome-icon far size="xs" icon="external-link-alt" class="ml-1"></font-awesome-icon></a>';
    }
}
