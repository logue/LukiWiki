<?php

/**
 * メールアドレス変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018,2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use Symfony\Polyfill\Intl\Idn\Idn;

// mailto: URL schemes
class Mailto extends AbstractInline
{
    protected $count = 5;

    protected $start = 0;

    public function __toString()
    {
        return '<a href="mailto:'.$this->name.'" rel="nofollow"><font-awesome-icon fas icon="envelope" class="mr-1"></font-awesome-icon>'.$this->alias.'</a>';
    }

    public function getPattern(): string
    {
        $s1 = $this->start + 1;

        return
            '(?:(?:\['.
                '(.[^\]\[]+)'.                          // [1] alias
            '\])'.
            '(?:'.
                '\('.
                    '([\w.-]+@)'.                       // [2] toname
                    '([^\/"<>\s]+\.[A-Za-z0-9-]+)'.     // [3] host
                    '(?:\s+(?:"(.*[^\(\)\[\]"]?)"))?'.  // [4] Title
                '\)'.
            ')'.
            '(?:\{'.
                '(.*[^\}]?)'.                           // [5] Body (option)
            '\})?)';
    }

    public function setPattern(array $arr, string $page = null): void
    {
        [$this->alias, $toname, $host, $this->title, $this->body] = $this->splice($arr);

        if (substr($host, 0, 4) === 'xn--') {
            $this->href = $toname.preg_replace('/'.$host.'/', Idn::idn_to_ascii($host, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), $this->href);
        }
        $this->href = $toname.$host;
    }
}
