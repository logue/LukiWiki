<?php
/**
 * InterWiki変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;

/**
 * URLs (InterWiki definition on "InterWikiName").
 */
class InterWiki extends AbstractInline
{
    public function getPattern()
    {
        return
        '\['.       // open bracket
        '('.        // (1) url
         '(?:(?:https?|ftp|ssh|telnet|):\/\/|\.\.?\/)[!~*\'();\/?:\@&=+\$,%#\w.-]*'.
        ')'.
        '\s'.
        '([^\]]+)'. // (2) alias
        '\]';       // close bracket
    }

    public function getCount()
    {
        return 2;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list(, $name, $alias) = $this->splice($arr);

        return parent::setParam($page, self::processText($name), null, $alias);
    }

    public function __toString()
    {
        $target = empty($this->redirect) ? $this->name : $this->redirect.rawurlencode($this->name);

        $purl = parse_url($target);
        if (isset($purl['host']) && extension_loaded('intl')) {
            // Fix punycode URL
            $url = preg_replace('/'.$purl['host'].'/', idn_to_ascii($purl['host']), $target);
        } else {
            $url = $target;
        }

        return parent::setLink($this->alias, $url, $this->name);
    }
}
