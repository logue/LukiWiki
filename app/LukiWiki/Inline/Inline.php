<?php
/**
 * インライン要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright (c)2018 by Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\Rules\InlineRules;
use App\LukiWIki\Utility\WikiFileSystem;

/**
 * インライン要素パースクラス.
 */
abstract class Inline
{
    protected $start;   // Origin number of parentheses (0 origin)
    protected $text;    // Matched string

    public $type;
    protected $page;
    protected $pages;
    public $name;
    protected $body;
    protected $alias;

    protected $redirect;

    protected $meta;

    /**
     * コンストラクタ
     *
     * @param string $start
     */
    public function __construct($start)
    {
        $this->start = $start;
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
     * @param type $arr
     * @param type $page
     */
    public function setPattern($arr, $page)
    {
        return '';
    }

    /**
     * 文字列化（インライン要素として帰ってくる.
     */
    public function __toString()
    {
        return $this->body;
    }

    // Private: Get needed parts from a matched array()
    public function splice($arr)
    {
        $count = $this->getCount() + 1;
        $arr = array_pad(array_splice($arr, $this->start, $count), $count, '');
        $this->text = $arr[0];

        return $arr;
    }

    // Set basic parameters
    public function setParam($page, $name, $body, $type = '', $alias = '')
    {
        $converter = new InlineConverter(['InlinePlugin']);

        $this->page = $page;
        $this->name = $name;
        $this->body = $body;
        $this->type = $type;
        if (!empty($alias)) {
            $alias = $converter->convert($alias, $page);
            $alias = preg_replace('#</?a[^>]*>#i', '', $alias);
            $this->alias = InlineRules::replace($alias);
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
    public function setAutoLink($page, $alias = '', $anchor = '', $refer = '', $isautolink = false)
    {
        $page = self::processText($page);
        if (empty($page)) {
            // ページ内リンク
            return '<a href="'.self::processText($anchor).'">'.self::processText($alias).'</a>';
        }
        $wikis = $this->pages;

        $anchor_name = empty($alias) ? $page : $alias;

        if (isset($wikis->$page)) {
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
     * @param string $uri     リンク先
     * @param string $tooltip title属性の内容
     * @param string $rel     リンクのタイプ
     *
     * @return string
     */
    public static function setLink($term, $uri, $tooltip = '', $rel = '', $is_redirect = false)
    {
        $_uri = self::processText($uri);
        $_term = self::processText($term);

        $_tooltip = !empty($tooltip) ? ' title="'.self::processText($tooltip).'"' : '';

        // rel = "*"を生成
        $rels[] = 'external';
        if (!empty($rel)) {
            $rels[] = $rel;
        }
        if ($is_redirect) {
            $rels[] = 'nofollow';
        }
        $ext_rel = implode(' ', $rels);

        return '<a href="'.$uri.'" rel="'.$rel.'"'.$_tooltip.'>'.$term.' <i class="fas fa-external-link-alt fa-xs"></i></a>';
        /*
                       // メディアファイル
                       if (!PKWK_DISABLE_INLINE_IMAGE_FROM_URI && Utility::isUri($uri)) {
                           if (preg_match(RendererDefines::IMAGE_EXTENTION_PATTERN, $uri)) {
                               // 画像の場合
                               $term = '<img src="'.$_uri.'" alt="'.$_term.'" '.$_tooltip.' />';
                           } else {
                               // 音声／動画の場合
                               $anchor = '<a href="'.$href.'" rel="'.(self::isInsideUri($uri) ? $rel : $ext_rel).'"'.$_tooltip.'>'.$_term.'</a>';
                               // 末尾のアイコン
                               $icon = self::isInsideUri($uri) ?
                                   '<a href="'.$href.'" rel="'.$rel.'">'.RendererDefines::INTERNAL_LINK_ICON.'</a>' :
                                   '<a href="'.$href.'" rel="'.$ext_rel.'">'.RendererDefines::EXTERNAL_LINK_ICON.'</a>';

                               if (preg_match(RendererDefines::VIDEO_EXTENTION_PATTERN, $uri)) {
                                   return '<video src="'.$_uri.'" alt="'.$_term.'" controls="controls"'.$_tooltip.'>'.$anchor.'</video>'.$icon;
                               } elseif (preg_match(RendererDefines::AUDIO_EXTENTION_PATTERN, $uri)) {
                                   return '<audio src="'.$_uri.'" alt="'.$_term.'" controls="controls"'.$_tooltip.'>'.$anchor.'</audio>'.$icon;
                               }
                           }
                       }

                       // リンクを出力
                       return self::isInsideUri($uri) ?
                           '<a href="'.$href.'" rel="'.$rel.'"'.$_tooltip.'>'.$term.RendererDefines::INTERNAL_LINK_ICON.'</a>' :
                           '<a href="'.$href.'" rel="'.$ext_rel.'"'.$_tooltip.'>'.$term.RendererDefines::EXTERNAL_LINK_ICON.'</a>'
        */
    }

    protected static function processText($str)
    {
        return htmlspecialchars(trim($str), ENT_HTML5, 'UTF-8');
    }

    public function getMeta()
    {
        return $this->meta;
    }
}
