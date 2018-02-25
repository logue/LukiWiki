<?php
/**
 * テーブルのセルクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

class TableCell extends Element
{
    protected $tag = 'td';    // {td|th}
    public $colspan = 1;
    public $rowspan = 1;
    public $style = [];         // is array('width'=>, 'align'=>...);
    public $is_blank = false;
    public $class = [];

    const CELL_OPTION_MATCH_PATTERN = '/^(?:(LEFT|CENTER|RIGHT|JUSTIFY)|(BG)?COLOR\(([#\w]+)\)|SIZE\((\d+)\)|LANG\((\w+2)\)|(TOP|MIDDLE|BOTTOM)|(NOWRAP)):(.*)$/';

    public function __construct($text, $is_template = false)
    {
        parent::__construct();
        $matches = [];

        // 必ず$matchesの末尾の配列にテキストの内容が入るのでarray_popの返り値を使用する方法に変更。
        // もうすこし、マシな実装方法ないかな・・・。12/05/03
        while (preg_match(self::CELL_OPTION_MATCH_PATTERN, $text, $matches)) {
            // 内容
            $text = array_pop($matches);
            // スイッチ
            if ($matches[1]) {
                // LEFT CENTER RIGHT JUSTIFY
                $this->style['text-align'] = $matches[1];
            } elseif ($matches[3]) {
                // COLOR / BGCOLOR
                $name = $matches[2] ? 'background-color' : 'color';
                $this->style[$name] = $matches[3];
            } elseif ($matches[4]) {
                // SIZE
                $this->style['font-size'] = $matches[4].'px';
            } elseif ($matches[5]) {
                // LANG
                $this->lang = strtolower(Utility::htmlsc($matches[5]));
            } elseif ($matches[6]) {
                // TOP / MIDDLE / BOTTOM
                // http://pukiwiki.sourceforge.jp/?%E8%B3%AA%E5%95%8F%E7%AE%B14%2F540
                $this->style['vertical-align'] = $matches[6];
            } elseif ($matches[7]) {
                // NOWRAP
                // http://pukiwiki.sourceforge.jp/dev/?PukiWiki%2F1.4%2F%A4%C1%A4%E7%A4%C3%A4%C8%CA%D8%CD%F8%A4%CB%2F%C9%BD%C1%C8%A4%DF%A4%C7nowrap%A4%F2%BB%D8%C4%EA%A4%B9%A4%EB
                $this->style['white-space'] = 'nowrap';
            }
        }
        if ($is_template && is_numeric($text)) {
            $this->style['width'] = $text.'px;';
        }

        if (preg_match("/\S+/", $text) === false) {
            // セルが空だったり、空白文字しか残らない場合は、空欄のセルとする。（HTMLではタブやスペースも削除）
            $text = '';
            $this->is_blank = true;
        } elseif ($text === '>') {
            $this->colspan = 0;
        } elseif ($text === '~') {
            $this->rowspan = 0;
        } elseif (substr($text, 0, 1) === '~') {
            $this->tag = 'th';
            $text = substr($text, 1);
        }

        if (!empty($text) && $text[0] === '#') {
            // Try using Div class for this $text
            $obj = ElementFactory::factory('Div', $this, $text);
            if ($obj instanceof Paragraph) {
                $obj = $obj->elements[0];
            }
        } else {
            $obj = ElementFactory::factory('InlineElement', null, $text);
        }

        $this->insert($obj);
    }

    public function setStyle(&$style)
    {
        foreach ($style as $key => $value) {
            if (!isset($this->style[$key])) {
                $this->style[$key] = $value;
            }
        }
    }

    public function toString()
    {
        if ($this->rowspan == 0 || $this->colspan == 0) {
            return '';
        }

        $param = ($this->is_blank === true ? ' class="blank-cell' : '');

        if ($this->rowspan > 1) {
            $param .= ' rowspan="'.$this->rowspan.'"';
        }
        if ($this->colspan > 1) {
            $param .= ' colspan="'.$this->colspan.'"';
            unset($this->style['width']);
        }
        if (!empty($this->lang)) {
            $param .= ' lang="'.$this->lang.'"';
        }

        if (!empty($this->style)) {
            $style = '';
            foreach ($this->style as $key => $value) {
                $style .= $key.':'.$value.';';
            }
            $param .= ' style="'.strtolower(strip_tags($style)).'"';
        }

        return $this->wrap(parent::toString(), $this->tag, $param, false);
    }
}
