<?php
/**
 * カンマ区切りのテーブルクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\Inline\InlineFactory;
use App\LukiWiki\Rules\BlockAlign;

/**
 * , cell1  , cell2  ,  cell3
 * , cell4  , cell5  ,  cell6
 * , cell7  ,   right,==
 * ,left          ,==,  cell8.
 */
class YTable extends Element
{
    protected $col;	// Number of columns

    public $align = 'center';

    // TODO: Seems unable to show literal '==' without tricks.
    //       But it will be imcompatible.
    // TODO: Why toString() or toXHTML() here
    public function __construct($row = ['cell1 ', ' cell2 ', ' cell3'])
    {
        parent::__construct();

        $str = [];
        $col = count($row);

        $matches = $_value = $_align = [];
        foreach ($row as $cell) {
            if (preg_match('/^(\s+)?(.+?)(\s+)?$/', $cell, $matches)) {
                if ($matches[2] == '==') {
                    // Colspan
                    $_value[] = false;
                    $_align[] = false;
                } else {
                    $_value[] = $matches[2];
                    if (empty($matches[1])) {
                        $_align[] = '';	// left
                    } elseif (isset($matches[3])) {
                        $_align[] = 'center';
                    } else {
                        $_align[] = 'right';
                    }
                }
            } else {
                $_value[] = $cell;
                $_align[] = '';
            }
        }

        for ($i = 0; $i < $col; ++$i) {
            if ($_value[$i] === false) {
                continue;
            }
            $colspan = 1;
            while (isset($_value[$i + $colspan]) && $_value[$i + $colspan] === false) {
                ++$colspan;
            }
            $colspan = ($colspan > 1) ? ' colspan="'.$colspan.'"' : '';
            $text = preg_match("/\S+/", $_value[$i]) ? InlineFactory::factory($_value[$i]) : '';
            $align = $_align[$i] ? ' class="text-'.$_align[$i].'"' : '';
            $str[] = '<td class="'.$class.'"'.$align.$colspan.'>'.$text.'</td>';
            unset($_value[$i], $_align[$i], $text);
        }

        $this->col = $col;
        $this->elements[] = implode('', $str);
    }

    public function canContain(&$obj)
    {
        return ($obj instanceof self) && ($obj->col == $this->col);
    }

    public function insert(&$obj)
    {
        $this->elements[] = $obj->elements[0];

        return $this;
    }

    public function toString()
    {
        $rows = '';
        foreach ($this->elements as $str) {
            $rows .= "\n".'<tr>'.$str.'</tr>'."\n";
        }

        return $this->wrap($rows, 'table', ['class' => 'table '.BlockAlign($this->align)]);
    }
}
