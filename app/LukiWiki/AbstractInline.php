<?php
/**
 * インライン要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright (c)2018-2019 by Logue
 * @license   MIT
 */

namespace App\LukiWiki;

use App\LukiWiki\Inline\InlineConverter;
use App\LukiWiki\Rules\InlineRules;
use App\Models\Page;
use Config;
use Symfony\Polyfill\Intl\Idn\Idn;

/**
 * インライン要素パースクラス.
 */
abstract class AbstractInline
{
    protected $start;   // Origin number of parentheses (0 origin)
    protected $text;    // Matched string

    protected $page;

    protected $name;
    protected $body;
    protected $href;
    protected $anchor;
    protected $alias;
    protected $title;
    protected $option;

    protected $redirect;

    protected $meta;

    /**
     * コンストラクタ
     *
     * @param int $start
     */
    public function __construct(int $start, string $page)
    {
        $this->start = $start;
        $this->page = $page;
    }

    /**
     * Wikiのパース用正規表現を取得.
     *
     * @return string
     */
    public function getPattern()
    {
        return '';
    }

    /**
     * 正規表現の(?: ...)などで帰ってくる値.
     *
     * @return int
     */
    public function getCount()
    {
        return 0;
    }

    /**
     * マッチするパターンを設定.
     *
     * @param array  $arr
     * @param string $page
     */
    public function setPattern(array $arr)
    {
        return '';
    }

    /**
     * 文字列化（インライン要素として帰ってくる.
     */
    public function __toString()
    {
        return trim($this->name);
    }

    /**
     * 正規表現の結果をパースする.
     *
     * @param array $arr
     */
    protected function splice(array $arr):array
    {
        $count = $this->getCount() + 1;
        $arr = array_pad(array_splice($arr, $this->start, $count), $count, '');
        $this->text = array_shift($arr);

        return $arr;
    }

    /**
     * リンクを貼る場合の処理.
     *
     * @param array $params パラメータ
     *
     * @return void
     */
    protected function setParam(array $params) :void
    {
        //$converter = new InlineConverter(['InlinePlugin'], []);

        //$meta = $converter->getMeta();
        if (!empty($meta)) {
            $this->meta = array_merge($this->meta, $meta);
        }

        if (preg_match('/^[https?|ftps?|git|ssh]/', $params['href'])) {
            $purl = parse_url($params['href']);
            if (isset($purl['host']) && substr($purl['host'], 0, 4) === 'xn--') {
                // 国際化ドメインのときにアドレスをpunycode変換する。（https://日本語.jp → https://xn--wgv71a119e.jp）
                $this->name = preg_replace('/'.$purl['host'].'/', Idn::idn_to_ascii($purl['host'], IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), $params['href']);
            }
        } else {
            $this->href = url(self::getPageName($params['href']));
        }

        $this->page = $params['page'] ?? $this->page;
        $this->title = $params['title'] ?? $this->page;
        $this->anchor = $params['anchor'] ?? null;
        $this->option = $params['option'] ?? null;
        $this->alias = $params['alias'] ?? null;

        //dd($this);

/*
        if (!empty($this->alias)) {
            $alias = $converter->convert($params['alias'], $params['page']);
            // aタグのみ削除
            $alias = preg_replace('#</?a[^>]*>#i', '', $alias);
            $this->alias = InlineRules::replace($alias);

        }
        */
    }

    /**
     * ページの自動リンクを作成.
     *
     * @param string $page       ページ名
     * @param string $alias      リンクの名前
     * @param string $anchor     ページ内アンカー（アドレスの#以降のテキスト）
     * @param string $refer      リンク元
     * @param bool   $isautolink 自動リンクか？
     *
     * @return string
     */
    public function setAutoLink():?string
    {
        if (!empty($this->page)) {
            $page = self::processText($this->page);
        }

        if (empty($page) && !empty($this->anchor)) {
            // ページ内リンク
            return '<a href="'.self::processText($this->anchor).'">'.self::processText($this->alias).'</a>';
        }

        $anchor_name = trim(empty($this->alias) ? $this->page : $this->alias);

        $title = !empty($this->title) ? $this->title : $this->page;

        if (in_array($page, Page::getEntries())) {
            return '<a href="'.url($page).$anchor.'" title="'.$title.'" v-b-tooltip>'.$anchor_name.'</a>';
        } elseif (!empty($page)) {
            $retval = $anchor_name.'<a href="'.url($page).':edit" rel="nofollow" title="Edit '.$page.'" v-b-tooltip>?</a>';

            return '<span class="bg-light text-dark">'.$retval.'</span>';
        }

        return $alias;
    }

    /**
     * リンクを作成（厳密にはimgタグ、audioタグ、videoタグにも使用するが）.
     *
     * @param string $term    リンクの名前
     * @param string $url     リンク先
     * @param string $tooltip title属性の内容
     * @param string $rel     リンクのタイプ
     *
     * @return string
     */
    public function setLink(?string $term = '', ?string $url = '', ?string $rel = '', bool $is_redirect = false)
    {
        $parsed_url = parse_url($url, PHP_URL_PATH);
        $_tooltip = !empty($this->title) ? ' title="'.$this->title.'"  v-b-tooltip' : '';
        if (!$parsed_url) {
            // パースできないURLだった場合リンクを貼らない。
            return self::processText($term);
        }

        // rel = "*"を生成
        $rels[] = 'external';
        if (!empty($rel)) {
            $rels[] = $rel;
        }
        if ($is_redirect) {
            $rels[] = 'nofollow';
        }
        $ext_rel = implode(' ', $rels);

        // リンクを出力
        return '<a href="'.$url.'" rel="'.$rel.'"'.$_tooltip.'>'.preg_replace('#</?a[^>]*>#i', '', $term).'<font-awesome-icon far size="xs" icon="external-link-alt" class="ml-1"></font-awesome-icon></a>';
    }

    /**
     * 相対指定のページ名から全ページ名を取得.
     *
     * @param string $name      名前の入力値
     * @param string $reference 引用元のページ名
     *
     * @return string ページのフルパス
     */
    public function getPageName(string $name = './')
    {
        $defaultpage = Config::get('lukiwiki.special_page.default');

        // 'Here'
        if (empty($name) || $name === './') {
            // ページ名が指定されてない場合、引用元のページ名を返す
            return $this->page;
        }
        //dd($this->page);

        // Absolute path
        if ($name[0] === '/') {
            $name = substr($name, 1);

            return empty($name) ? $defaultpage : $name;
        }

        // Relative path from 'Here'
        if (substr($name, 0, 2) === './') {
            // 同一ディレクトリ
            $arrn = preg_split('#/#', $name, -1, PREG_SPLIT_NO_EMPTY);
            $arrn[0] = $this->page;

            return implode('/', $arrn);
        }

        // Relative path from dirname()
        if (substr($name, 0, 3) === '../') {
            // 上の階層
            $arrn = preg_split('#/#', $name, -1, PREG_SPLIT_NO_EMPTY);
            $arrp = preg_split('#/#', $this->page, -1, PREG_SPLIT_NO_EMPTY);

            // 階層を遡る
            while (!empty($arrn) && $arrn[0] === '..') {
                array_shift($arrn);
                array_pop($arrp);
            }
            // ディレクトリを結合する
            $name = !empty($arrp) ? implode('/', array_merge($arrp, $arrn)) :
                (!empty($arrn) ? $defaultpage.'/'.implode('/', $arrn) : $defaultpage);
        }

        return $name;
    }

    /**
     * パスを含まないページ名を取得.
     *
     * @param $page ページ名
     */
    public static function getPageNameShort($page)
    {
        $pagestack = explode('/', $page);

        return array_pop($pagestack);
    }

    /**
     * 文字列をエスケープ.
     *
     * @param string $str
     *
     * @return string
     */
    protected static function processText(string $str)
    {
        return htmlspecialchars(trim($str), ENT_HTML5, 'UTF-8');
    }

    /**
     * メタ情報を取得.
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
