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
    public function __construct(int $start, bool $isAmp = false)
    {
        $this->start = $start;
        $this->isAmp = $isAmp;
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
    public function setPattern(array $arr, ?string $page = null)
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
            $this->href = url($params['href']);
        }

        $this->page = $params['page'] ?? null;
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
    public function setAutoLink(?string $page, ?string $alias = '', ?string $anchor = '', string $refer = '', bool $isautolink = false):?string
    {
        if (!empty($page)) {
            $page = self::processText($page);
        }

        if (empty($page) && !empty($anchor)) {
            // ページ内リンク
            return '<a href="'.self::processText($anchor).'">'.self::processText($alias).'</a>';
        }

        $anchor_name = trim(empty($alias) ? $page : $alias);

        $title = !empty($this->title) ? $this->title : $page;

        if (in_array($page, Page::getEntries())) {
            return '<a href="'.url($page).$anchor.'"'.
                ($isautolink === true ? ' class="autolink"' : '').' title="'.$title.'" v-b-tooltip>'.$anchor_name.'</a>';
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
        return '<a href="'.$url.'" rel="'.$rel.'"'.$_tooltip.'>'.$term.'<font-awesome-icon far size="xs" icon="external-link-alt" class="ml-1"></font-awesome-icon></a>';
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
