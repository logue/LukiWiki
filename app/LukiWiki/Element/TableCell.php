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

    const CELL_OPTION_MATCH_PATTERN = '/^(?:(LEFT|CENTER|RIGHT|JUSTIFY)|(BG)?COLOR\(([#\w]+)\)|SIZE\((\w+)\)|LANG\((\w+2)\)|(BASELINE|TOP|MIDDLE|BOTTOM|TEXT-TOP|TEXT-BOTTOM)|(NOWRAP)(TRUNCATE)):(.*)$/';

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
                $this->class[] = 'text-'.self::processParam($matches[1]);
            } elseif ($matches[3]) {
                // COLOR / BGCOLOR
                $name = $matches[2] ? 'background-color' : 'color';
                $this->style[$name] = self::processParam($matches[3]);
            } elseif ($matches[4]) {
                // SIZE
                $value = self::processParam($matches[4]);
                if (is_numeric($value)) {
                    // 10px = 1rem
                    $this->style['font-size'] = (int) $value * 0.1.'rem';
                } elseif (preg_match('/^h[1-6]$', $value)) {
                    // h1 ~ h6
                    $this->class[] = $value;
                }
            } elseif ($matches[5]) {
                // LANG
                $this->lang = self::processParam($matches[5]);
            } elseif ($matches[6]) {
                // BASELINE / TOP / MIDDLE / BOTTOM / TEXT-TOP / TEXT~BOTTOM
                $this->class[] = 'align-'.self::processParam($matches[6]);
            } elseif ($matches[7]) {
                // NOWRAP
                $this->class[] = 'text-nowrap';
            } elseif ($matches[8]) {
                // TRUNCATE（長いテキストを省略）
                $this->class[] = 'text-truncate';
            }
        }
        if ($is_template) {
            // テンプレート行（末尾にhを入れるヘッダー行の前の行の処理）
            if (is_numeric($text)) {
                $this->style['width'] = (int) $text * 0.1.'rem';
            } elseif (preg_match('/\d+%$/', $text)) {
                // %指定
                $this->style['width'] = (int) $text.'%';
            }
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
            $obj = new InlineElement($text);
        }

        $this->meta = $obj->getMeta();
        $this->insert($obj);
    }

    public function toString()
    {
        $param = [];
        if ($this->rowspan > 1) {
            $param['rowspan'] = $this->rowspan;
        }
        if ($this->colspan > 1) {
            $param['colspan'] = $this->colspan;
            unset($this->style['width']);
        }
        if (!empty($this->lang)) {
            $param['lang'] = $this->lang;
        }

        if (!empty($this->style)) {
            $style = [];
            foreach ($this->style as $key => $value) {
                $style[] = $key.':'.$value;
            }
            $param['style'] = implode(';', $style);
        }

        if (!empty($this->class)) {
            $param['class'] = implode(' ', $this->class);
        }

        return $this->wrap(parent::toString(), $this->tag, $param, false);
    }
}
