<?php

/**
 * テーブルのセルクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018,2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;

class TableCell extends AbstractElement
{
    /** @var string セルのパラメータの正規表現 */
    //const CELL_OPTION_MATCH_PATTERN = '^(?:(LEFT|CENTER|RIGHT|JUSTIFY|BASELINE|TOP|MIDDLE|BOTTOM|TEXT-TOP|TEXT-BOTTOM|NOWRAP|TRUNCATE|((BG)?COLOR|SIZE|LANG)(?:\((\w+)\)))):.+$';
    /** @var int 縦連結 */
    public $colspan = 1;
    /** @var int 横連結 */
    public $rowspan = 1;
    /** @var array スタイル属性 */
    public $style = [];         // is array('width'=>, 'align'=>...);
    /** @var bool 空っぽのセルか */
    protected $is_blank = false;
    /** @var array セルのクラス */
    protected $class = [];
    /** @var string セルのタグ名 */
    protected $tag;    // {td|th|caption}
    /** @var string 言語 */
    protected $lang = '';

    /**
     * コンストラクタ
     *
     * @param string $text
     * @param string $tag
     * @param bool   $is_template
     * @param string $page
     */
    public function __construct(string $text, bool $is_template, string $page)
    {
        parent::__construct();

        if ($is_template) {
            // テンプレート行（末尾にhを入れるヘッダー行の前の行の処理）
            if (is_numeric($text)) {
                $this->style['width'] = $text . 'rem';
            } elseif (preg_match('/\d+%$/', $text)) {
                // %指定
                $this->style['width'] = $text . '%';
            }

            return;
        }

        if (trim($text) === '') {
            // セルが空だったり、空白文字しか残らない場合は、空欄のセルとする。（HTMLではタブやスペースも削除）
            $this->is_blank = true;

            return;
        }
        if ($text === '>') {
            // 列連結
            $this->colspan = 0;

            return;
        }
        if ($text === '^') {
            // 行連結
            $this->rowspan = 0;

            return;
        }
        if (substr($text, 0, 1) === '~') {
            // ヘッダーセル
            $this->tag = 'th';
            $text = substr($text, 1);
        } elseif (strpos($text, ':') !== false) {
            // :が含まれていた場合末尾をテキストとし、それ以外をパラメータとして処理をする。
            $matches = explode(':', $text);
            // 内容
            $text = array_pop($matches);
            // パラメータをパース
            $matches = explode(',', $matches);
            // 配列の先端から順に評価する
            while ($match = array_shift($matches)) {
                // 大文字にする
                switch (strtoupper($match)) {
                    case 'LEFT':
                    case 'CENTER':
                    case 'RIGHT':
                    case 'JUSTIFY':
                        // 水平位置
                        $this->class['align'] = 'text-' . strtolower(self::processText($match));
                        break;
                    case 'BASELINE':
                    case 'TOP':
                    case 'MIDDLE':
                    case 'BOTTOM':
                    case 'TEXT-TOP':
                    case 'TEXT-BOTTOM':
                        // 垂直位置
                        $this->class['valign'] = 'align-' . strtolower(self::processText($match));
                        break;
                    case 'NOWRAP':
                        // 回り込み禁止
                        $this->class['nowrap'] = 'text-nowrap';
                        break;
                    case 'TRUNCATE':
                        // 長いテキストを省略
                        $this->class['truncate'] = 'text-truncate';
                        break;
                    case preg_match('/^(\w+)\((.+)\)$/', $match, $m2) === 1:
                        // パラメータ付き指定
                        $value = self::processText($m2[2]);
                        switch ($m2[1]) {
                            case 'BGCOLOR':
                                // セルの背景色
                                $this->style['background-color'] = $value;
                                break;
                            case 'COLOR':
                                // セルの文字色
                                $this->style['color'] = $value;
                                break;
                            case 'SIZE':
                                // セルの文字サイズ
                                if (is_numeric($value)) {
                                    // 単位が含まれていない場合、rem表記とする
                                    $this->style['font-size'] = (int) $value . 'rem';
                                // TODO:数値は制限したほうがいい？
                                } elseif (preg_match('/^h[1-6]$', $value)) {
                                    // h1～h6が入力されていた場合、bootstrapのヘッダーの文字サイズとする
                                    $this->class['h'] = $value;
                                }
                                // あえて%指定はできないようにする。
                                break;
                            case 'LANG':
                                // セルの言語
                                $this->lang = $value;
                                break;
                        }
                        break;
                }
            }
        }

        $this->tag = $tag;

        // テキストはインライン変換の処理を行う
        $obj = new InlineElement($text, $page);
        $this->meta = $obj->getMeta();
        $this->insert($obj);
    }

    public function __toString()
    {
        $param = [];
        if ($this->rowspan > 1) {
            // 行連結
            $param['rowspan'] = $this->rowspan;
        }
        if ($this->colspan > 1) {
            // 列連結
            $param['colspan'] = $this->colspan;
            // 幅が指定されていた場合、連結後の幅になるので幅設定を省略
            unset($this->style['width']);
        }
        if (!empty($this->lang)) {
            // セルの言語設定
            $param['lang'] = $this->lang;
        }

        if (!empty($this->style)) {
            // スタイルシート
            $style = [];
            foreach ($this->style as $key => $value) {
                $style[] = $key . ': ' . $value;
            }
            $param['style'] = implode(';', $style);
        }

        if (!empty($this->class)) {
            // クラス
            $param['class'] = implode(' ', $this->class);
        }

        return $this->wrap(parent::__toString(), $this->tag, $param, false);
    }

    /**
     * スタイルシートをセット.
     *
     * @param array $style
     */
    private function setStyle(array $style): void
    {
        foreach ($style as $key => $value) {
            if (!isset($this->style[$key])) {
                $this->style[$key] = $value;
            }
        }
    }
}
