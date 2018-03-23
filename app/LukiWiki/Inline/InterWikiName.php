<?php
/**
 * InterWikiName変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\Rules\InlineRules;
use App\LukiWIki\Utility\WikiFileSystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * InterWikiName-rendered URLs.
 */
class InterWikiName extends Inline
{
    const INTERWIKINAME_PATTERN = '/\[((?:(?:https?|ftp|news):\/\/|\.\.?\/)[!~*\'();\/?:\@&=+\$,%#\w.-]*)\s([^\]]+)\]\s?([^\s]*)/';
    const INTERWIKINAME_CACHE_NAME = 'interwikiname';
    const INTERWIKINAME_MAX_LENGTH = 512;

    private static $encode_aliases = [
        'sjis'  => 'SJIS',
        'euc'   => 'EUC-JP',
        'utf8'  => 'UTF-8',
        'gbk'   => 'CP936',
        'euckr' => 'EUC-KR',
        'big5'  => 'BIG5',
    ];

    protected $url = '';
    protected $param = '';
    protected $anchor = '';

    public function getPattern()
    {
        $s2 = $this->start + 2;
        $s5 = $this->start + 5;

        return
            '\[\['.                  // open bracket
            '(?:'.
             '((?:(?!\]\]).)+)>'.    // (1) alias
            ')?'.
            '(\[\[)?'.               // (2) open bracket
            '((?:(?!\s|:|\]\]).)+)'. // (3) InterWiki
            '(?<! > | >\[\[ )'.      // not '>' or '>[['
            ':'.                     // separator
            '('.                     // (4) param
             '(\[\[)?'.              // (5) open bracket
             '(?:(?!>|\]\]).)+'.
             '(?('.$s5.')\]\])'. // close bracket if (5)
            ')'.
            '(?('.$s2.')\]\])'.  // close bracket if (2)
            '\]\]';                  // close bracket
    }

    public function getCount()
    {
        return 5;
    }

    public function setPattern(array $arr, string $page = null)
    {
        list(, $alias, , $name, $this->param) = $this->splice($arr);
        $this->name = $name;
        $this->alias = $alias;
        $matches = [];
        if (preg_match('/^([^#]+)(#[A-Za-z][\w-]*)$/', $this->param, $matches)) {
            list(, $this->param, $this->anchor) = $matches;
        }

        $url = self::getInterWikiUrl($name, $this->param);
        if (empty($url)) {
            return $name.':'.$this->param;
        }
        $this->url = parent::processText($url);

        return parent::setParam(
            $page,
            parent::processText($name.':'.$this->param),
            null,
            empty($alias) ? $name.':'.$this->param : $alias
        );
    }

    public function __toString()
    {
        $url = (empty($this->redirect) ? $this->url : $this->redirect.rawurlencode($this->url)).$this->anchor;
        if (empty($url)) {
            return '<span title="'.$this->name.'" class="bg-warning"><i class="fas fa-globe"></i> '.$this->alias.'</span>';
        }

        return '<a href="'.$target.$this->anchor.'" title="'.$this->name.'" rel="nofollow"><i class="fas fa-globe"></i> '.$this->alias.'</a>';
    }

    /**
     * InterWikiNameをURLに変換する.
     *
     * @param string $name  InterWiki名
     * @param string $param InterWikiに送るパラメータ
     *
     * @return string
     */
    public static function getInterWikiUrl($name, $param = '')
    {
        $interwikinames = self::getInterWikiNameDict();

        if (!isset($interwikinames[$name])) {
            return false;
        }

        list($url, $opt) = $interwikinames[$name];

        if (!empty($param)) {
            // Encoding
            switch ($opt) {
                case '':    /* FALLTHROUGH */
                case 'std': // Simply URL-encode the string, whose base encoding is the internal-encoding
                    $param = rawurlencode($param);
                    break;

                case 'asis': /* FALLTHROUGH */
                case 'raw': // Truly as-is
                    break;

                case 'yw': // YukiWiki
                    if (!preg_match('/'.InlineRules::WIKINAME_PATTERN.'/', $param)) {
                        $param = '[['.mb_convert_encoding($param, 'SJIS', 'UTF-8').']]';
                    }
                    break;

                case 'moin': // MoinMoin
                    $param = str_replace('%', '_', rawurlencode($param));
                    break;

                default:
                    // Alias conversion of $opt
                    if (isset($encode_aliases[$opt])) {
                        $opt = $encode_aliases[$opt];
                    }

                    // Encoding conversion into specified encode, and URLencode
                    $param = rawurlencode(mb_convert_encoding($param, $opt, 'UTF-8'));
            }

            // Replace or Add the parameter
            if (strpos($url, '$1') !== false) {
                $url = str_replace('$1', $param, $url);
            //$url = strtr($url, '$1', $param);
            } else {
                $url .= $param;
            }
        }

        $len = strlen($url);
        if ($len > self::INTERWIKINAME_MAX_LENGTH) {
            throw new \Exception('InterWiki URL too long: '.$len.' characters');
        }

        return $url;
    }

    /**
     * InterWikiNameページから辞書を作成する.
     *
     * @param bool $force キャッシュを再生成する
     *
     * @return array
     */
    private static function getInterWikiNameDict()
    {
        $pages = WikiFileSystem::getInstance();
        $interwikiname = Config::get('lukiwiki.special_page.interwikiname');
        if (empty($interwikiname) || !isset($pages->$interwikiname)) {
            return;
        }

        return Cache::remember(self::INTERWIKINAME_CACHE_NAME, null, function () {
            $interwikinames = $matches = [];
            $lines = $pages->$interwikiname;
            foreach ($lines as $line) {
                if (preg_match(self::INTERWIKINAME_PATTERN, $line, $matches)) {
                    $interwikinames[$matches[2]] = [$matches[1], $matches[3]];
                }
            }

            return $interwikinames;
        });
    }
}
