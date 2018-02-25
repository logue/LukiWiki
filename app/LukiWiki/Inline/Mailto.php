<?php
/**
 * メールアドレス変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

// mailto: URL schemes
class Mailto extends Inline
{
    public function __construct($start)
    {
        parent::__construct($start);
    }

    public function getPattern()
    {
        $s1 = $this->start + 1;

        return
            '(?:'.
             '\[\['.
             '((?:(?!\]\]).)+)(?:>|:)'.     // (1) alias
            ')?'.
            '([\w.-]+@)'.                   // (2) toname
            '([^\/"<>\s]+\.[A-Za-z0-9-]+)'. // (3) host
            '(?('.$s1.')\]\])';	        // close bracket if (1)
    }

    public function getCount()
    {
        return 3;
    }

    public function setPattern($arr, $page)
    {
        list(, $alias, $toname, $host) = $this->splice($arr);
        $name = $orginalname = $toname.$host;
        if (extension_loaded('intl')) {
            // 国際化ドメイン対応
            if (preg_match('/[^A-Za-z0-9.-]/', $host)) {
                $name = $toname.idn_to_ascii($host);
            } elseif (!$alias && strtolower(substr($host, 0, 4)) === 'xn--') {
                $orginalname = $toname.idn_to_utf8($host);
            }
        }

        return parent :: setParam($page, $name, '', 'mailto', $alias === '' ? $orginalname : $alias);
    }

    public function __toString()
    {
        return '<a href="mailto:'.$this->name.'" rel="nofollow">'.$this->alias.'</a>';
    }
}
