<?php
/**
 * テーブルクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\Rules\Alignment;

/**
 * | title1 | title2 | title3 |
 * | cell1  | cell2  | cell3  |
 * | cell4  | cell5  | cell6  |.
 */
class Table extends AbstractElement
{
    protected $type;
    protected $types;
    protected $col;   // number of column
    public $align = 'CENTER';
    protected static $parts = [
        'h' => 'thead',
        'f' => 'tfoot',
        ''  => 'tbody',
    ];

    public function __construct($out, $isAmp)
    {
        parent::__construct();

        $cells = explode('|', $out[1]);
        $this->col = count($cells);
        $this->type = strtolower($out[2]);
        $this->types = [$this->type];
        $is_template = $this->type === 'c';
        $row = [];
        foreach ($cells as $cell) {
            $row[] = new TableCell($cell, $is_template, $isAmp);
        }
        $this->elements[] = $row;
    }

    public function canContain($obj)
    {
        return $obj instanceof self && $obj->col === $this->col;
    }

    public function insert($obj)
    {
        $this->elements[] = $obj->elements[0];
        $this->types[] = $obj->type;

        return $this;
    }

    public function __toString()
    {
        // Set rowspan (from bottom, to top)
        for ($ncol = 0; $ncol < $this->col; ++$ncol) {
            $rowspan = 1;
            foreach (array_reverse(array_keys($this->elements)) as $nrow) {
                $row = $this->elements[$nrow];
                if ($row[$ncol]->rowspan === 0) {
                    ++$rowspan;
                    continue;
                }
                $row[$ncol]->rowspan = $rowspan;
                // Inherits row type
                while (--$rowspan) {
                    $this->types[$nrow + $rowspan] = $this->types[$nrow];
                }
                $rowspan = 1;
            }
        }

        // Set colspan and style
        $stylerow = null;
        foreach (array_keys($this->elements) as $nrow) {
            $row = $this->elements[$nrow];
            if ($this->types[$nrow] === 'c') {
                $stylerow = $row;
            }
            $colspan = 1;
            foreach (array_keys($row) as $ncol) {
                if ($row[$ncol]->colspan === 0) {
                    ++$colspan;
                    continue;
                }
                $row[$ncol]->colspan = $colspan;
                if (!is_null($stylerow)) {
                    $row[$ncol]->setStyle($stylerow[$ncol]->style);
                    // Inherits column style
                    while (--$colspan) {
                        $row[$ncol - $colspan]->setStyle($stylerow[$ncol]->style);
                    }
                }
                $colspan = 1;
            }
        }

        // toString
        $string = '';
        foreach (static::$parts as $type => $part) {
            $part_string = '';
            foreach (array_keys($this->elements) as $nrow) {
                if ($this->types[$nrow] !== $type) {
                    continue;
                }
                $row = $this->elements[$nrow];
                $row_string = '';
                foreach (array_keys($row) as $ncol) {
                    $row_string .= $row[$ncol];
                }
                $part_string .= $this->wrap($row_string, 'tr', [], false);
            }
            $string .= $this->wrap($part_string, $part, [], false);
        }
        $align = Alignment::block($this->align);

        return $this->wrap($string, 'table', ['class' => 'table table-bordered '.$align], false);
    }
}
