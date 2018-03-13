<?php
/**
 * ブロック要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

/**
 * Block elements.
 */
class Element
{
    protected $parent;
    protected $elements;    // References of childs
    protected $last;        // Insert new one at the back of the $last
    protected $meta = null;

    public function __construct()
    {
        $this->elements = [];
        $this->last = $this;
    }

    public function __destruct()
    {
        unset($this->elements);
        unset($this->last);
        unset($this->meta);
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function add($obj)
    {
        if ($this->canContain($obj)) {
            return $this->insert($obj);
        }

        return $this->parent->add($obj);
    }

    public function insert($obj)
    {
        if (is_object($obj)) {
            $obj->setParent($this);
            $this->elements[] = $obj;

            $this->last = $obj->last;
        }

        return $this->last;
    }

    public function canContain($obj)
    {
        return true;
    }

    public function wrap($string, $tag, $param = [], $canomit = true)
    {
        $attributes = [];
        foreach ($param as $key => $value) {
            $attributes[] = $key.'="'.$value.'"';
        }

        return ($canomit && empty($string)) ? '' :
            '<'.$tag.(count($attributes) !== 0 ? ' '.implode(' ', $attributes) : '').'>'.trim($string).'</'.$tag.'>';
    }

    public function toString()
    {
        $ret = [];
        $keys = array_keys($this->elements);
        foreach ($keys as $key) {
            $ret[] = $this->elements[$key]->toString();
        }

        return implode("\n", $ret);
    }

    public function getMeta()
    {
        return $this->meta;
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
}
