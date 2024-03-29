<?php

/**
 * ブロック要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki;

/**
 * Block Abstracts.
 */
abstract class AbstractElement
{
    protected $parent;

    protected $last;        // Insert new one at the back of the $last

    protected $meta;

    protected static $converter;

    protected $elements = [];    // References of childs

    protected $isAmp = false;

    protected $page;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->elements = [];
        $this->last = $this;
        $this->init();
    }

    /**
     * デストラクタ
     */
    public function __destruct()
    {
        $this->finalize();
        unset($this->elements, $this->last, $this->meta);
    }

    /**
     * 変換結果を出力.
     */
    public function __toString()
    {
        $ret = [];
        foreach ($this->elements as $obj) {
            $ret[] = $obj->__toString();
        }

        return implode("\n", $ret);
    }

    public function init()
    {
    }

    public function finalize()
    {
    }

    /**
     * 親要素に挿入.
     *
     * @param  object  $obj
     */
    public function setParent(object $parent)
    {
        $this->parent = $parent;
    }

    /**
     * 親要素に要素を追加.
     *
     * @param  object  $obj
     */
    public function add(object $obj)
    {
        if ($this->canContain($obj)) {
            return $this->insert($obj);
        }
        if (\is_object($this->parent)) {
            return $this->parent->add($obj);
        }
    }

    /**
     * 要素を追加.
     *
     * @param  object  $obj
     */
    public function insert(object $obj)
    {
        if (\is_object($obj)) {
            $obj->setParent($this);
            $this->elements[] = $obj;

            $this->last = $obj->last;
        }

        return $this->last;
    }

    /**
     * 小要素を持つことができるか.
     *
     * @param  object  $obj
     * @return bool
     */
    public function canContain(object $obj)
    {
        return $obj instanceof self;
    }

    /**
     * タグで包む
     *
     * @param  string  $string  子要素
     * @param  string  $tag     タグ名
     * @param  array  $param   タグに入れる属性
     * @param  bool  $canomit
     * @return string
     */
    public function wrap(string $innerHtml, string $tag, array $param, bool $canomit)
    {
        $attributes = [];
        foreach ($param as $key => $value) {
            if (empty($value)) {
                $attributes[] = $key;

                continue;
            }
            $attributes[] = $key.'="'.$value.'"';
        }

        return ($canomit && empty($string)) ? '' :
            '<'.$tag.(\count($attributes) !== 0 ? ' '.implode(' ', $attributes) : '').'>'.trim($innerHtml).'</'.$tag.'>';
    }

    /**
     * メタデータを取得.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * 文字列をエスケープ.
     *
     * @param  string  $str
     * @return string
     */
    protected static function processText(string $str): string
    {
        return htmlspecialchars(
            trim(str_replace("\r", "\n", $str)),
            ENT_HTML5,
            'UTF-8'
        );
    }
}
