<?php
/**
 * インライン要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright (c)2018 by Logue
 * @license   MIT
 */

namespace App\LukiWiki;

use App\LukiWiki\Rules\InlineRules;
use App\LukiWIki\Utility\WikiFileSystem;
use Illuminate\Support\Facades\Config;

/**
 * インライン要素パースクラス.
 */
abstract class AbstractInline
{
    protected $start;   // Origin number of parentheses (0 origin)
    protected $text;    // Matched string

    protected $page;
    protected $pages;
    public $name;
    protected $body;
    protected $alias;

    protected $redirect;

    protected $meta;

    protected $isAmp;

    /**
     * コンストラクタ
     *
     * @param int $start
     */
    public function __construct(int $start, bool $isAmp)
    {
        $this->start = $start;
        $this->isAmp = $isAmp;
        $this->pages = WikiFileSystem::getInstance();
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
    public function setPattern(array $arr, string $page = null)
    {
        return '';
    }

    /**
     * 文字列化（インライン要素として帰ってくる.
     */
    public function __toString()
    {
        return trim($this->body);
    }

    /**
     * 正規表現の結果をパースする.
     *
     * @param array $arr
     */
    protected function splice(array $arr)
    {
        $count = $this->getCount() + 1;
        $arr = array_pad(array_splice($arr, $this->start, $count), $count, '');
        $this->text = $arr[0];

        return $arr;
    }

    // Set basic parameters
    public function setParam(string $page, string $name, string $body, string $alias = '')
    {
        $converter = new InlineConverter(['InlinePlugin'], [], $this->isAmp);

        $meta = $converter->getMeta();
        if (!empty($meta)) {
            $this->meta = array_merge($this->meta, $meta);
        }

        $this->page = $page;
        $this->name = $name;
        $this->body = $body;
        if (!empty($alias)) {
            $alias = $converter->convert($alias, $page);
            // aタグのみ削除
            $alias = preg_replace('#</?a[^>]*>#i', '', $alias);
            $this->alias = InlineRules::replace($alias);
            $this->meta = $converter->getMeta();
        } else {
            $this->alias = $body;
        }

        return true;
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
    public function setAutoLink(string $page, string $alias = '', string $anchor = '', string $refer = '', bool $isautolink = false)
    {
        $page = self::processText($page);
        if (empty($page)) {
            // ページ内リンク
            return '<a href="'.self::processText($anchor).'">'.self::processText($alias).'</a>';
        }
        $wikis = $this->pages;

        $anchor_name = empty($alias) ? $page : $alias;

        if (isset($wikis->$page)) {
            $this->meta['pages'] = $page;

            return '<a href="'.url($page).$anchor.'" data-timestamp="'.$wikis->timestamp($page).'"'.
                ($isautolink === true ? ' class="autolink"' : '').' title="'.$page.'">'.$anchor_name.'</a>';
        } else {
            $retval = $anchor_name.'<a href="'.url($page).'?action=edit" rel="nofollow" title="'.$page.'">?</a>';

            return '<span class="bg-light text-dark">'.$retval.'</span>';
        }
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
    public function setLink(string $term, string $url, string $tooltip = '', string $rel = '', bool $is_redirect = false)
    {
        $parsed_url = parse_url($url, PHP_URL_PATH);
        $_tooltip = !empty($tooltip) ? ' title="'.self::processText($tooltip).'"' : '';
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

        // メディアファイル
        if (Config::get('lukiwiki.render.expand_external_media_file')) {
            // 拡張子を取得
            $ext = substr($parsed_url, strrpos($parsed_url, '.') + 1);

            switch ($ext) {
                case 'jpeg':
                case 'jpg':
                case 'gif':
                case 'png':
                case 'svg':
                case 'svgz':
                case 'webp':
                case 'bmp':
                case 'ico':
                    if ($this->isAmp) {
                        $term = '<amp-img src="'.$url.'" alt="'.self::processText($term).'" width="1" height="1" class="external-media"><div fallback>'.$tooltip.'</div></amp-img>';
                    } else {
                        $term = '<img src="'.$url.'" alt="'.self::processText($term).'" '.$_tooltip.' />';
                    }

                    break;
                case 'mp4':
                case 'ogm':
                case 'webm':
                    if ($this->isAmp) {
                        return '<amp-video src="'.$url.'" controls '.$_tooltip.' width="1" height="1" class="external-media"><div fallback>'.self::processText($term).'</div></amp-video>';
                    } else {
                        return '<video src="'.$url.'" alt="'.self::processText($term).'" controls="controls"'.$_tooltip.'/>';
                    }
                     break;
                case 'wav':
                case 'ogg':
                case 'm4a':
                case 'mp3':
                    if ($this->isAmp) {
                        return '<amp-audio  src="'.$url.'" controls '.$_tooltip.' width="auto" height="50"><div fallback>'.self::processText($term).'</div></amp-audio>';
                    } else {
                        return '<audio src="'.$url.'" alt="'.self::processText($term).'" controls="controls"'.$_tooltip.'/>';
                    }
                    break;
            }
        }

        // リンクを出力
        return '<a href="'.$url.'" rel="'.$rel.'"'.$_tooltip.'>'.$term.' <i class="fas fa-external-link-alt fa-xs"></i></a>';
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
