<?php
/**
 * InterWiki変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013,2014,2018,2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;
use Symfony\Polyfill\Intl\Idn\Idn;

/**
 * URLs (InterWiki definition on "InterWikiName").
 */
class InterWiki extends AbstractInline
{
    public function getPattern()
    {
        // [Link Text](URL "title")
        return
            '\['.
                '((?:(?!\]).)+)'. // [1] Link Text
            '\]'.
            '\('.
                '('.    // [2] Link to
                    '(?:https?|ftp|ssh)'.  // protocol
                    '(?::\/\/[-_.!~*\'a-zA-Z0-9;\/?:\@&=+\$,%#]+)'. // path, port, etc
                ')'.    // [2] Link to end
                '(?:\s{1,}?"'.
                    '((?:(?!"\)).)+)'. // [3] title
                '")?'.
            '\)';
    }

    public function getCount()
    {
        return 3;
    }

    public function setPattern(array $arr, string $page = null)
    {
        //dd($this->getPattern(), $this->splice($arr));
        list(, $alias, $name, $title) = $this->splice($arr);

        if (empty($name)) {
            return parent::setParam($alias, $alias, $alias);
        }

        return parent::setParam('', $name, '', $alias, $title);
    }

    public function __toString()
    {
        $target = empty($this->redirect) ? $this->name : $this->redirect.rawurlencode($this->name);

        $purl = parse_url($target);
        if (isset($purl['host']) && substr($purl['host'], 0, 4) === 'xn--') {
            // 国際化ドメインのときにアドレスをpunycode変換する。（https://日本語.jp → https://xn--wgv71a119e.jp）
            $url = preg_replace('/'.$purl['host'].'/', Idn::idn_to_ascii($purl['host'], IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), $target);
        } else {
           $url = $target;
        }

        return parent::setLink($this->alias, $url, $this->name);
    }
}
