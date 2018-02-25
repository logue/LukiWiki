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

    public function __construct()
    {
        $this->elements = [];
        $this->last = &$this;
    }

    public function setParent(&$parent)
    {
        $this->parent = &$parent;
    }

    public function add(&$obj)
    {
        if ($this->canContain($obj)) {
            return $this->insert($obj);
        } else {
            return $this->parent->add($obj);
        }
    }

    public function insert(&$obj)
    {
        if (gettype($obj) === 'object') {
            $obj->setParent($this);
            $this->elements[] = $obj;

            $this->last = &$obj->last;
        }

        return $this->last;
    }

    public function canContain(&$obj)
    {
        return true;
    }

    public function wrap($string, $tag, $param = [], $canomit = true)
    {
        $attributes = [];
        if ($param == '') {
            dd($tag);
        }
        foreach ($param as $key => $value) {
            $attributes[] = $key.'="'.$value.'"';
        }

        try {
            return ($canomit && empty($string)) ? '' :
            '<'.$tag.(count($attributes) !== 0 ? ' '.implode(' ', $attributes) : '').'>'.$string.'</'.$tag.'>';
        } catch (\Exception $e) {
            dd($tag, $param, $string);
        }
    }

    public function toString()
    {
        $ret = [];
        foreach (array_keys($this->elements) as $key) {
            $ret[] = $this->elements[$key]->toString();
        }

        return implode("\n", $ret);
    }

    public function dump($indent = 0)
    {
        $ret = str_repeat(' ', $indent).get_class($this)."\n";
        $indent += 2;
        foreach (array_keys($this->elements) as $key) {
            $ret .= is_object($this->elements[$key]) ?
                $this->elements[$key]->dump($indent) : null;
            //str_repeat(' ', $indent) . $this->elements[$key];
        }

        return $ret;
    }
}
