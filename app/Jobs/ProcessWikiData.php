<?php

/**
 * PukiWikiデータの変換および取り込みのメイン処理.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Jobs;

use App\Enums\InterWikiType;
use App\Models\InterWiki;
use App\Models\Page;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessWikiData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * 最大試行回数.
     *
     * @var int
     */
    public $tries = 1;

    public $page;

    private $file;

    private $created_at;

    private $updated_at;

    /**
     * Create a new job instance.
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        $this->page = hex2bin(pathinfo($this->file, PATHINFO_FILENAME));
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Loading "'.$this->file.'"...');

        // この値は使用しないため移行しない
        if (substr($this->page, 0, 3) === ':log' || substr($this->page, 0, 9) === 'PukiWiki/') {
            Log::info('Skipped "'.$this->page.'".');

            return;
        }

        // :が含まれるページ名は_に変更する。
        $pagename = str_replace(':', '_', $this->page);
        $data = explode("\n", /* @scrutinizer ignore-type */ Storage::get($this->file));

        // Storageクラスに作成日を取得する関数がないためファイルの実体のパスを取得
        $from = str_replace('\\', DIRECTORY_SEPARATOR, storage_path('app/'.$this->file));

        // タイムスタンプを取得
        $this->created_at = Carbon::createFromTimestamp(filectime($from))->format('Y-m-d H:i:s');
        $this->updated_at = Carbon::createFromTimestamp(Storage::lastModified($this->file))->format('Y-m-d H:i:s');

        // InterWikiName、AutoAliasName、Glossaryは別に処理
        switch ($this->page) {
            case 'InterWikiName':
                $this->interwiki($data);

                return;
            case 'AutoAliasName':
                $this->autoalias($data);

                return;
            case 'Glossary':
                $this->glossary($data);

                return;
            default:
                $ret = self::pukiwiki2lukiwiki($data);
                break;
        }

        Log::info('Save "'.$pagename.'" to DB.');

        // if ($page !== 'Web素材') {
        //    continue;
        // }
        Page::updateOrCreate(
            [
                // 更新対象
                'name' => $pagename,
            ],
            [
                'source' => $ret['source'],
                'title' => $ret['title'],
                'description' => $ret['description'],
                'locked' => $ret['locked'],
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        );
    }

    /**
     * 失敗したジョブの処理.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Convert Error: '.$this->page);
        Log::error($exception->__toString());
    }

    /**
     * PukiWiki文法をLukiWiki文法に変換.
     */
    private static function pukiwiki2lukiwiki(array $lines): array
    {
        $title = null;
        $freezed = false;
        $description = null;
        $pre = [];
        $ret = [];

        // LukiWiki文法に変換
        while (! empty($lines)) {
            // 1行ずつ処理
            $line = array_shift($lines);

            // 先頭１文字を取得
            $char = substr($line, 0, 1);

            // プラグイン（改行を一旦CRにして1行に貯め込む） #plugin{{ ... }}
            if (preg_match('/^#[^{]+(\{\{+)\s*$/', $line, $matches) !== 0) {
                $len = \strlen($matches[1]);
                $line .= "\r";
                while (! empty($lines)) {
                    $next_line = preg_replace('/[\r\n]*$/', '', array_shift($lines));
                    if (preg_match('/^\}{'.$len.'}$/', $next_line)) {
                        $line .= $next_line;
                        break;
                    }
                    $line .= $next_line .= "\r";
                }
            }

            // 整形済みテキストの場合、一旦$pre変数に溜め込んで、あとで```でくくって処理する。
            if ($char === ' ' || $char === "\t") {
                $pre[] = rtrim(substr($line, 1));

                continue;
            }
            if (substr($line, 0, 2) === '# ' || substr($line, 0, 2) === "#\t") {
                // PukiWiki Plus互換
                $pre[] = rtrim(substr($line, 2));

                continue;
            }
            // 整形済みテキストのエリアが終了したら一気に書き込む
            if (! empty($pre)) {
                $tmp = trim(implode("\n", $pre));
                // 統合してトリムして空白しか残らなかった場合は処理しない。
                if (! empty($tmp)) {
                    $ret[] = '```'."\n".$tmp."\n".'```';
                }
            }
            $pre = [];

            if (substr($line, 0, 6) === 'TITLE:') {
                $title = substr($line, 6);

                continue;
            }

            $line = preg_replace_callback('/(?:SIZE\((\d+)\))/u', function ($matches) {
                // サイズはrem指定に変更
                return 'SIZE('.self::px2rem((int) $matches[1]).')';
            }, $line);

            // リンクの形式変更（LukiWikiでは[...](...)という形式。添付ファイルとの区別は!でする）
            $line = preg_replace_callback('/(?:\[{2}(.+?)\]{2})/u', function ($matches) {
                if (! isset($matches[1])) {
                    return '';
                }
                $tmp = explode(':', $matches[1]);

                if (preg_match('/^(.+)>(.+)$/u', $matches[1], $m) !== 0) {
                    // [[foo>bar]]、[[foo>bar:fiz]]、[[foo>http://aaa]]
                    return '['.$m[1].']('.$m[2].')';
                }
                if (\count($tmp) > 2) {
                    // 正規表現でやるのがめんどうなのでexplodeで:で分割して最初の値がリンク名、残りはアドレスという扱い
                    // [[foo:bar]]、[[foo:http://aaa]]
                    return '['.array_shift($tmp).']('.implode(':', $tmp).')';
                }

                // 通常のリンク（Braketが1個になるだけだが・・・）
                return '['.$matches[1].']';
            }, $line);

            // 打ち消し線
            $line = preg_replace('/%{2}(.+)%{2}/u', ' ~~${1}~~ ', $line);
            // コード
            $line = preg_replace('/@{2}(.+)@{2}/u', ' `${1}` ', $line);
            // イタリック
            $line = preg_replace('/\'{3}(.+)\'{3}/u', ' *${1}* ', $line);
            // 強調
            $line = preg_replace('/\'{2}(.+)\'{2}/u', ' **${1}** ', $line);

            // インライン型プラグイン
            $line = preg_replace_callback(
                '/&((\w+)(?:\(((?:(?!\)[;{]).)*)\))?)(?:\{((?:(?R)|(?!};).)*)\})?;/u',
                function ($matches) {
                    $plugin = $matches[2];
                    $option = isset($matches[3]) ? explode(',', trim($matches[3])) : [];
                    $body = isset($matches[4]) ? trim($matches[4]) : null;

                    return self::processPlugin('&', $plugin, $option, $body);
                },
                $line
            );

            switch ($char) {
                // ブロック型プラグイン
                case '#':
                    preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(?:\{\{*(.+?)\}\}*)?/', $line, $matches);
                    $plugin = trim($matches[1]);
                    $option = isset($matches[2]) ? explode(',', trim($matches[2])) : [];
                    $body = isset($matches[3]) ? trim($matches[3]) : null;
                    // dd($line, $matches, $body);

                    if ($plugin === 'freeze') {
                        // 凍結フラグ
                        $freezed = true;
                    } elseif ($plugin === 'description') {
                        // 説明文
                        $description = $option[0];
                    } else {
                        $ret[] = self::processPlugin('@', $plugin, $option, $body);
                        $ret[] = '';
                    }

                    break;
                case '*':
                    // 見出しの整形
                    if (preg_match('/^(\*{1,3})(.+)\s\[\#(\w+)\]?$/s', $line, $matches) !== 0) {
                        $level = \strlen($matches[1]);
                        // *を# に変換する。
                        $ret[] = str_repeat('#', $level).' '.trim($matches[2]).
                            // アンカーが付いていた場合は[#アンカー名]にする。
                            (isset($matches[3]) ? (' [#'.trim($matches[3]).']') : '');
                        // また改行を設ける。
                        $ret[] = '';
                        $matches = [];
                    }
                    break;
                case '-':
                case '+':
                    if (preg_match('/^([\+|\-]{1,3})(.+)$/s', $line, $matches) !== 0) {
                        $level = \strlen($matches[1]);
                        $text = trim($matches[2]);
                        $ret[] = str_repeat(' ', $level - 1).$char.' '.$text;
                        $matches = [];
                    }
                    break;
                case '|':
                    if (preg_match('/\|(.+)\|(\w+)$/i', $line, $matches) !== 0) {
                        // オプション
                        $option = strtolower($matches[2]);
                        // テーブル定義行の処理
                        if (isset($matches[1]) && strpos($option, 'c') !== false) {
                            $cells = explode('|', $matches[1]);
                            $c = [];
                            foreach ($cells as $cell) {
                                if (strpos($cell, ':') !== false) {
                                    // セルにパラメータが含まれている場合
                                    $params = explode(':', trim($cell));
                                    $p = [];
                                    foreach ($params as $param) {
                                        if (is_numeric($param)) {
                                            // 数値の場合remに変換
                                            $p[] = self::px2rem((int) $param);
                                        } else {
                                            $p[] = $param;
                                        }
                                    }
                                    $c[] = implode(':', $p);
                                } else {
                                    $c[] = trim($cell);
                                }
                            }
                            if ($option === 'c') {
                                $option = 't';
                            }
                            $ret[] = '|'.implode('|', $c).'|'.$option;
                        } else {
                            // cが含まれていない場合、そのまま移行
                            $ret[] = $line;
                        }
                    } else {
                        // そのまま移行
                        $ret[] = $line;
                    }
                    break;
                default:
                    // 他の行は右トリム
                    $ret[] = rtrim($line);
                    break;
            }
        }

        return [
            'source' => implode("\n", $ret),
            'locked' => $freezed,
            'title' => $title,
            'description' => $description,
        ];
    }

    /**
     * プラグインの処理.
     *
     * @param  string  $char  識別子（@か&のみ）
     * @param  string  $plugin  プラグイン名
     * @param  array  $option  引数 ()内
     * @param  string  $body  中身 {}内
     * @return string
     */
    private static function processPlugin(string $char, string $plugin, array $options = [], ?string $body = null)
    {
        // #プラグイン名(引数){中身} or &プラグイン名(引数){中身};
        // ※帰り値の末尾に;を入れないこと。
        if (! in_array($char, ['&', '@'])) {
            throw new Exception('Unknown plugin char:'.$char);
        }

        // ブロック型か
        $isBlock = $char === '@';

        switch ($plugin) {
            case 'aname':
                if (! empty($body)) {
                    return '['.$body.'](#'.$options[0].')';
                }

                return '[#'.$options[0].']';
            case 'new':
                // 新着
                $t = preg_replace('/\((.+)\)/u', '', $body);
                try {
                    $dt = Carbon::parse($t);

                    return $char.'timestamp('.$dt->timestamp.');';
                } catch (Exception $e) {
                    // Carbonで処理できない数値はそのまま移行（trackerのテンプレートなど）
                    return $char.'timestamp('.$t.');';
                }
            case 'size':
                // サイズはrem単位に変換する。
                return $char.'size('.self::px2rem((int) $options[0]).'){'.$body.'};';
            case 'color':
                return $char.'color('.$options[0].'){'.$body.'};';
            case 'epoch':
                // 時差を考慮した新着（Adv.）
                return $char.'timestamp('.$options[0].');';
            case 'tooltip':
                // ツールチップはabbrに
                if (! empty($body)) {
                    return $char.'abbr('.$options[0].'){'.$body.'};';
                }
                // no break
            case 'hr':
                return '----';
            case 'br':
                return '&br;';
            case 'pre':
            case 'sh':
            case 'code':
            case 'highlight':
                $lang = trim($options[0]);
                // TODO:code.inc.phpの対応言語をcodemirrorにマッピング
                if (strpos($lang, 'html') !== false) {
                    $lang = 'htmlmixed';
                } elseif ($lang === 'pukiwiki') {
                    $lang = 'plain';
                }

                return '```'.$lang."\n".$body."\n".'```';
            case 'img':
            case 'attach':
            case 'attachref':
            case 'ref':
                // 添付ファイルの形式
                // PanDoc準拠。
                // ![タイトル](ファイル名){クラス width=幅 height=高さ}
                // ※IDはサポートしない。
                $file = array_shift($options);   // 一番最初の配列にはファイル名が入る。

                if (empty($file)) {
                    // 回り込み解除
                    return '@clear';
                }

                if (isset($options[1]) && preg_match('/\[{2}([^\]{2}].+)?\]{2}/u', $options[1], $m)) {
                    // 古い形式の添付ファイル（#ref(ファイル名, [[ページ名]], ...);という形式
                    $file = $m[1].'/'.$file;
                    unset($options[1]);
                }

                if (in_array(['noimg', 'novideo', 'noaudio'], $options, true)) {
                    // メディアファイルを展開しないオプションが含まれていた場合、単純なリンクを出力
                    return '['.$file.']';
                }

                $title = '';
                $params = [];
                foreach (array_unique($options) as $option) {
                    switch ($option) {
                        case 'nolink':
                        case 'zoom':
                            break;
                        case 'around':
                            $isBlock = true;
                            break;
                        case 'left':
                        case 'center':
                        case 'right':
                        case 'justify':
                            if ($isBlock) {
                                // ブロック型出ない場合は無視する
                                $params['float'] = $option;
                            }
                            break;
                        case 'rounded':
                        case 'circle':
                        case 'thumbnail':
                            $params['type'] = $option;
                            break;
                        default:
                            if (preg_match('/^([0-9]+%?)(?:x([0-9]+%?))?$/', $option, $m)) {
                                if (strpos($m[1], '%')) {
                                    // 50%など%を含んでいた場合片方だけを倍率として使う
                                    $params['zoom'] = $m[1];
                                } elseif (isset($m[2])) {
                                    // 640x480のような指定
                                    $params['width'] = $m[1];
                                    $params['height'] = $m[2];
                                }
                            } else {
                                // そうでない場合タイトルとして処理
                                $title = $option;
                            }
                    }
                }

                // パラメータ（LukiWiki拡張書式）
                // {width: 640, height: 480}
                $param = ! empty($params) ? '{'.str_replace('=', ':', http_build_query($params, '', ', ')).'}' : '';

                if ($isBlock) {
                    // ブロック型だった場合
                    return '!['.$title.']('.$file.')'.$param;
                } elseif (empty($title) || preg_match('/\.(gif|jpe?g|a?png|bmp|svg|webp|avif|ico|tif?f)$/i', $file)) {
                    // imgタグで表示可能なファイルだった場合
                    return '&image('.$file.(! empty($title) ? ', '.$title : '').')'.$param.';';
                } else {
                    // 展開できないファイルだった場合はリンクに（パラメータは無視）
                    return '['.$title.']('.$file.')';
                }
            case 'ruby':
                // ルビはoptionとbodyを逆転させる　&ruby(ルビの内容){対象}; →　&ruby(対象){ルビの内容};
                // tooltipの仕様と合わせる。LaTeX互換。
                return $char.'ruby('.$body.'){'.$options[0].'};';
            case 'ls':
            case 'ls2':
            case 'ls3':
                return '@ls'.isset($options) ? '('.implode(',', $options).')' : '';
            case 'floatclear':
                return '@clear';
            case 'edit':
            case 'counter':
            case 'norelated':
            case 'tboff':
            case 'menu':
            case 'nomenubar':
            case 'nofollow':
            case 'nosidebar':
            case 'keywords':
            case 'interwiki':
            case 'lastmod':
            case 'lookup':
            case 'paint':
            case 'server':
            case 'stationary':
            case 'version':
            case 'versionlist':
                // 無視するプラグイン
                return '/* '.'deprecated plugin:"'.$plugin.'" param:'.
                    implode(',', $options).' body:'.$body.' */';
        }
        if ($isBlock && ! empty($body) || strpos($body, "\r") !== false) {
            // 複数行の場合
            $body = trim(str_replace("\r", "\n", $body));
            if (! empty($body)) {
                $body = '{'."\n".$body."\n".'}';
            }
        }

        return $char.$plugin.
            (! empty($options) ? '('.implode(',', $options).')' : '').
            (! empty($body) ? '{'.$body.'}' : '');
    }

    /**
     * pxをremに変換.
     *
     * @param  int  $px  ピクセル
     */
    private static function px2rem(int $px): float
    {
        return round($px / 16, 5);
    }

    /**
     * InterWikiNameをインポート.
     */
    private function interwiki(array $lines)
    {
        Log::info('Process InterWikiName.');
        foreach ($lines as $line) {
            if (preg_match('/\[((?:(?:https?|ftp|news):\/\/|\.\.?\/)[!~*\'();\/?:\@&=+\$,%#\w.-]*)\s([^\]]+)\]\s?([^\s]*)/', $line, $matches) !== false) {
                $name = isset($matches[2]) ? trim($matches[2]) : null;
                $value = isset($matches[1]) ? trim($matches[1]) : null;
                $encode = isset($matches[3]) ? trim($matches[3]) : null;
                if (empty($name) || empty($value)) {
                    continue;
                }
                InterWiki::updateOrCreate(
                    [
                        'name' => $name,
                        'type' => InterWikiType::InterWikiName,
                    ],
                    [
                        'value' => $value,
                        'encode' => $encode,
                        'created_at' => $this->created_at,
                        'updated_at' => $this->updated_at,
                    ]
                );
            }
        }
        Log::info('InterWikiName done.');
    }

    /**
     * AutoAliasNameをインポート.
     */
    private function autoalias(array $lines)
    {
        Log::info('Process AutoAliasName.');
        foreach ($lines as $line) {
            if (preg_match('/\[\[((?:(?!\]\]).)+)>((?:(?!\]\]).)+)\]\]/', $line, $matches, PREG_SET_ORDER) !== false) {
                $name = isset($matches[1]) ? trim($matches[1]) : null;
                $value = isset($matches[2]) ? trim($matches[2]) : null;
                if (empty($name) || empty($value)) {
                    continue;
                }
                InterWiki::updateOrCreate(
                    [
                        'name' => $name,
                        'type' => InterWikiType::AutoAliasName,
                    ],
                    [
                        'value' => $value,
                        'created_at' => $this->created_at,
                        'updated_at' => $this->updated_at,
                    ]
                );
            }
        }
        Log::info('AutoAliasName done.');
    }

    /**
     * Glossaryをインポート.
     */
    private function glossary(array $lines)
    {
        Log::info('Process Glossary.');
        foreach ($lines as $line) {
            if (preg_match('/^[:|]([^|]+)\|([^|]+)\|?$/', $line, $matches) !== false) {
                $name = isset($matches[1]) ? trim($matches[1]) : null;
                $value = isset($matches[2]) ? trim($matches[2]) : null;
                if (empty($name) || empty($value)) {
                    continue;
                }
                InterWiki::updateOrCreate(
                    [
                        'name' => $name,
                        'type' => InterWikiType::Glossary,
                    ],
                    [
                        'value' => self::pukiwiki2lukiwiki([$value])['source'],
                        'created_at' => $this->created_at,
                        'updated_at' => $this->updated_at,
                    ]
                );
            }
        }
        Log::info('Glossary done.');
    }
}
