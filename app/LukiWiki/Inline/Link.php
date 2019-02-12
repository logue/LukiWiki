<?php
/**
 * リンク変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use Symfony\Polyfill\Intl\Idn\Idn;

/**
 * [alt](URL or WikiName "title"){option}.
 */
class Link extends AbstractInline
{
    public function getPattern()
    {
        return
            '(?:(?:\['.
                '(.[^\]\[]+)'.                          // [1] alias
            '\])'.
            '(?:'.
                '\('.
                   '(.[^\r\n\t\f\[\]#&"\(\)]+?)'.       // [2] Name
                   '(?:\#(\w[^\#]+?))?'.                // [3] Anchor
                   '(?:\s+(?:"(.*[^\(\)\[\]"]?)"))?'.   // [4] Title
                '\)'.
            ')'.
            '(?:\{'.
                '(.*[^\}]?)'.                           // [5] Body (option)
            '\})?)';
    }

    public function getCount()
    {
        return 5;
    }

    public function setPattern(array $arr, string $page = null)
    {
        //dd($this->getPattern(), $arr, $this->splice($arr));
        list($this->alias, $this->href, $this->anchor, $this->title, $this->body) = $this->splice($arr);
    }

    public function __toString()
    {
        $purl = parse_url($this->href);
        if (isset($purl['host']) && substr($purl['host'], 0, 4) === 'xn--') {
            // 国際化ドメインのときにアドレスをpunycode変換する。（https://日本語.jp → https://xn--wgv71a119e.jp）
            $url = preg_replace('/'.$purl['host'].'/', Idn::idn_to_ascii($purl['host'], IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), $this->href);
        } else {
            // ページリンク
            $url = $this->href;
        }

        return parent::setLink($this->alias, $url, $this->name);
    }
}