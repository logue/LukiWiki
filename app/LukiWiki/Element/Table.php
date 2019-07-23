<?php
/**
 * テーブルクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018,2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;
use App\LukiWiki\Rules\Alignment;

/**
 * | title1 | title2 | title3 |
 * | cell1  | cell2  | cell3  |
 * | cell4  | cell5  | cell6  |.
 */
class Table extends AbstractElement
{
    /** @var string デフォルトのテーブルの位置 */
    public $align = 'CENTER';
    /** @var string */
    protected $type;
    protected $types;
    /** @var int 列の数 */
    protected $col;
    /** @var array セルの種類 */
    protected static $parts = [
        // ヘッダー行
        'h' => 'thead',
        // フッター行
        'f' => 'tfoot',
        // キャプション
        'c' => 'caption',
        // 通常の行
        ''  => 'tbody',
    ];

    /**
     * コンストラクタ
     *
     * @param string $input
     * @param string $page
     */
    public function __construct(string $input, string $page)
    {
        parent::__construct();

        $cells = explode('|', $input);
        $last_cell = strtolower(array_pop($cells));

        if ($last_cell === 't' || $last_cell === 'h' || $last_cell === 'f' || $last_cell === 'c') {
            // T…テンプレート行、H…ヘッダー行、F…フッター行、C…キャプション
            $this->type = $last_cell;
            $cells = array_pop($cells);
        }
        // 列数
        $this->col = \count($cells);
        // セルのタイプ
        $this->types = [$this->type];

        $row = [];
        if ($this->type !== 'c') {
            // セルの行ごとにセル内を処理
            foreach ($cells as $cell) {
                $row[] = new TableCell($cell, $this->type === 't', $page);
            }
        } else {
            // キャプション（最初の値のみ取得。多分使う人はいない）
            $row[] = new TableCaption($cells[0], $page);
        }
        $this->elements[] = $row;
    }

    public function __toString()
    {
        // Set rowspan (from bottom, to top)
        for ($ncol = 0; $ncol < $this->col; $ncol++) {
            $rowspan = 1;
            foreach (array_reverse(array_keys($this->elements)) as $nrow) {
                $row = $this->elements[$nrow];
                if ($row[$ncol]->rowspan === 0) {
                    $rowspan++;
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
                    $colspan++;
                    continue;
                }
                $row[$ncol]->colspan = $colspan;
                if (null !== $stylerow) {
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
}
