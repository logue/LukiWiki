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

    protected $count = 0;

    protected $redirect;

    protected $meta;

    /**
     * コンストラクタ
     *
     * @param  int  $start
     */
    final public function __construct(int $start, ?string $page)
    {
        $this->start = $start;
        $this->page = $page;
    }

    /**
     * 文字列化（インライン要素として帰ってくる.
     */
    public function __toString()
    {
        return trim($this->name);
    }

    /**
     * Wikiのパース用正規表現を取得.
     *
     * @return string
     */
    public function getPattern(): ?string
    {
        return null;
    }

    /**
     * 正規表現の(?: ...)などで帰ってくる値.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * マッチするパターンを設定.
     *
     * @param  array  $arr
     */
    public function setPattern(array $arr): void
    {
    }

    /**
     * ページの自動リンクを作成.
     *
     * @return string
     */
    public function setAutoLink(): ?string
    {
        if (empty($this->page) && ! empty($this->anchor)) {
            // ページ内リンク
            return '<a href="'.$this->anchor.'">'.$this->alias.'</a>';
        }

        $anchor_name = trim(empty($this->alias) ? $this->page : $this->alias);

        $title = ! empty($this->title) ? $this->title : $this->page;

        if (\in_array($this->page, array_keys(Page::getEntries()), true)) {
            return '<a href="'.url($this->page).$this->anchor.'" title="'.$title.'" v-b-tooltip>'.$anchor_name.'</a>';
        }
        if (! empty($this->page)) {
            $retval = $anchor_name.'<a href="'.url($this->page).':edit" rel="nofollow" title="Edit '.$this->page.'" v-b-tooltip>?</a>';

            return '<span class="bg-light text-dark">'.$retval.'</span>';
        }

        return $this->alias;
    }

    /**
     * 相対指定のページ名から全ページ名を取得.
     *
     * @param  string  $name      名前の入力値
     * @param  string  $reference 引用元のページ名
     * @return string ページのフルパス
     */
    public function getPageName(string $name = './'): string
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
            while (! empty($arrn) && $arrn[0] === '..') {
                array_shift($arrn);
                array_pop($arrp);
            }
            // ディレクトリを結合する
            $name = ! empty($arrp) ? implode('/', array_merge($arrp, $arrn)) : (! empty($arrn) ? $defaultpage.'/'.implode('/', $arrn) : $defaultpage);
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
     * メタ情報を取得.
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * 正規表現の結果をパースする.
     *
     * @param  array  $arr
     */
    protected function splice(array $arr): array
    {
        $count = $this->getCount() + 1;
        $arr = array_pad(array_splice($arr, $this->start, $count), $count, '');
        $this->text = array_shift($arr);

        return $arr;
    }

    /**
     * リンクを貼る場合の処理.
     *
     * @param  array  $params パラメータ
     */
    protected function setParam(array $params): void
    {
        //$converter = new InlineConverter(['InlinePlugin'], []);

        //$meta = $converter->getMeta();
        if (! empty($meta)) {
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

        $this->page = self::processText($params['page'] ?? $this->page);
        $this->title = self::processText($params['title'] ?? $this->page);
        $this->anchor = self::processText($params['anchor'] ?? null);
        $this->option = self::processText($params['option'] ?? null);
        $this->alias = self::processText($params['alias'] ?? null);

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
     * 文字列をエスケープ.
     *
     * @param  string  $str
     * @return string
     */
    protected static function processText(?string $str): ?string
    {
        return $str ? htmlspecialchars(trim($str), ENT_HTML5, 'UTF-8') : null;
    }
}
