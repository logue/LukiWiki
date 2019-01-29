<?php
/**
 * PukiWiki形式をLukiWiki形式に変換.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Utility;

use App\Models\Attachment;
use App\Models\Page;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class Converter
{
    /**
     * コンストラクタ
     *
     * @param string $path PukiWikiのデーターのディレクトリへのパス
     */
    public function __construct(string $path)
    {
        // 各ディレクトリを取得
        $this->wiki_dir = $path.'/wiki/';
        $this->attach_dir = $path.'/attach/';
        $this->counter_dir = $path.'/counter/';
        $this->backup_dir = $path.'/backup/';

        $this->attachments_dir = Config::get('lukiwiki.directory.attach');
    }

    /**
     * Wikiディレクトリの内容変換.
     */
    public function wiki()
    {
        Log::debug('Start Wiki data convertion.');

        foreach (Storage::files($this->wiki_dir) as &$file) {
            // ページ名
            $page = hex2bin(pathinfo($file, PATHINFO_FILENAME));

            if (empty($page)) {
                // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
                continue;
            }

            Log::debug('Import '.$file.'... ('.$page.')');

            // :configから始まるページ名はPukiWikiのプラグインの初期設定で使う。
            // この値は使用しないため移行しない
            if (substr($page, 0, 7) === ':config' || substr($page, 0, 3) === ':log' || substr($page, 0, 9) === 'PukiWiki/') {
                continue;
            }
            // :が含まれるページ名は_に変更する。
            $page = preg_replace('/\:/', '_', $page);
            $data = explode("\n", rtrim(Storage::get($file)));

            $ret = self::pukiwiki2lukiwiki($data);

            try {
                $created = Carbon::createFromTimestamp(filectime(storage_path($file)));
            } catch (\Exception $e) {
                //dd($e);
                $created = null;
            }

            //if ($page !== 'Web素材') continue;
            Page::updateOrCreate(
                [
                    // 更新対象
                    'name'        => $page, 
                ], [
                    'user_id'     => 0,
                    'source'      => implode("\n", $ret['source']),
                    'title'       => $ret['title'],
                    'description' => $ret['description'],
                    'locked'      => $ret['locked'],
                    'status'      => 0,
                    'ip'          => $_SERVER['REMOTE_ADDR'],
                    'created_at'  => $created,
                    'updated_at'  => Carbon::createFromTimestamp(Storage::lastModified($file)),
                ]
        );
        }
        Log::debug('Finish.');
    }
    /**
     * PukiWiki文法をLukiWiki文法に変換.
     *
     * @param array $lines
     *
     * @return array
     */
    private static function pukiwiki2lukiwiki(array $lines)
    {
        $title = null;
        $freezed = false;
        $description = null;
        $ori = $lines;

        // LukiWiki文法に変換
        while (!empty($lines)) {
            // 1行ずつ処理
            $line = array_shift($lines);

            // 先頭１文字を取得
            $char = substr($line, 0, 1);

            // プラグイン（改行を一旦CRにして1行に貯め込む） #plugin{{ ... }}
            if (preg_match('/^#[^{]+(\{\{+)\s*$/', $line, $matches) !== 0) {
                $len = strlen($matches[1]);
                $line .= "\r";
                while (!empty($lines)) {
                    $next_line = preg_replace('/[\r\n]*$/', '', array_shift($lines));
                    if (preg_match('/\}{'.$len.'}/', $next_line)) {
                        $line .= $next_line;
                        break;
                    } else {
                        $line .= $next_line .= "\r";
                    }
                }
            }

            // 整形済みテキストの場合、一旦$pre変数に溜め込んで、あとで```でくくって処理する。
            if ($char === ' ' || $char === "\t") {
                $pre[] = rtrim(substr($line, 1));
                continue;
            } elseif (substr($line, 0, 2) === '# ' || substr($line, 0, 2) == "#\t") {
                // PukiWiki Plus互換
                $pre[] = rtrim(substr($line, 2));
                continue;
            } else {
                // 整形済みテキストのエリアが終了したら一気に書き込む
                if (!empty($pre)) {
                    $ret[] = '```'."\n".trim(implode("\n", $pre))."\n".'```';
                }
                unset($pre);
            }

            if (substr($line, 0, 7) === 'TITLE:'){
                $title = substr($line,-8);
            }

            // リンクの形式変更（LukiWikiでは[...](...)という形式）
            $line = preg_replace_callback('/\[{2}(.+?)\]{2}/u', function ($matches) {
                if (!isset($matches[1])) {
                    return '';
                }
                $tmp = explode(':', $matches[1]);

                if (preg_match('/^(.+)>(.+)$/u', $matches[1], $m) !== 0) {
                    // [[foo>bar]]、[[foo>bar:fiz]]、[[foo>http://aaa]]
                    return '['.$m[1].']('.$m[2].')';
                } elseif (count($tmp) > 2) {
                    // 正規表現でやるのがめんどうなのでexplodeで:で分割して最初の値がリンク名、残りはアドレスという扱い
                    // [[foo:bar]]、[[foo:http://aaa]]
                    return '['.array_shift($tmp).']('.implode(':', $tmp).')';
                } else {
                    // 通常のリンク（Braketが1個になるだけだが・・・）
                    return '['.$matches[1].']';
                }
            }, $line);

            // 打ち消し線
            $line = preg_replace('/%{2}(.+)%{2}/u', '~~${1}~~', $line);
            // コード
            $line = preg_replace('/@{2}(.+)@{2}/u', '`${1}`', $line);
            // イタリック
            $line = preg_replace('/\'{3}(.+)\'{3}/u', '** ${1} **', $line);
            // 強調
            $line = preg_replace('/\'{2}(.+)\'{2}/u', '* ${1} *', $line);

            // インライン型プラグイン
            $line = preg_replace_callback('/&((\w+)(?:\(((?:(?!\)[;{]).)*)\))?)(?:\{((?:(?R)|(?!};).)*)\})?;/u', function ($matches) {
                $plugin = $matches[2];
                $option = isset($matches[3]) ? explode(',', trim($matches[3])) : [];
                $body = isset($matches[4]) ? trim($matches[4]) : null;

                return self::processPlugin('&', $plugin, $option, $body).';';
            },
                $line
            );

            switch ($char) {
                // ブロック型プラグイン
                case '#':
                    preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(?:\{\{*(.+?)\}\}*)?/', $line, $matches);
                    $plugin = trim($matches[1]);
                    $option = isset($matches[2]) ? explode(',', trim($matches[2])) : [];
                    $body = isset($matches[3]) ? str_replace("\r", "\n", trim($matches[3])) : null;
                    //dd($line, $matches, $body);

                    if ($plugin === 'freeze') {
                        // 凍結フラグ
                        $freezed = true;
                    } elseif ($plugin === 'description') {
                        // 説明文
                        $description = $option[0];
                    } else {
                        $ret[] = self::processPlugin('@', $plugin, $option, $body);
                    }

                    break;
                case '*':
                    // 見出しの整形
                    if (preg_match('/^(\*{1,3})(.+)\s\[\#(\w+)\]?$/s', $line, $matches) !== 0) {
                        $level = strlen($matches[1]);
                        // *を# に変換する。
                        $ret[] = str_repeat('#', $level).' '.trim($matches[2]).(isset($matches[3]) ? (' [#'.trim($matches[3]).']') : '');
                        // また改行を設ける。
                        $ret[] = '';
                        $matches = [];
                    }
                    break;
                case '-':
                case '+':
                    if (preg_match('/^([\+|\-]{1,3})(.+)$/s', $line, $matches) !== 0) {
                        $level = strlen($matches[1]);
                        $text = trim($matches[2]);
                        $ret[] = str_repeat(' ', $level-1).$char.' '.$text;
                        $matches = [];
                    }
                    break;
                default:
                    // 他の行は右トリム
                    $ret[] = rtrim($line);
                    break;
            }
        }

        return ['source' => $ret, 'locked' => $freezed, 'title' => $title, 'description' => $description];
    }

    /**
     * プラグインの処理.
     *
     * @param string $char   識別子
     * @param string $plugin プラグイン名
     * @param array  $option 引数 ()内
     * @param string $body   中身 {}内
     *
     * @return string
     */
    private static function processPlugin(string $char, string $plugin, array $option = [], string $body = null)
    {
        // #プラグイン名(引数){中身} or &プラグイン名(引数){中身};
        // ※帰り値の末尾に;を入れないこと。
        switch ($plugin) {
            case 'aname':
                if (!empty($body)) {
                    return  '['.$body.'](#'.$option[0].')';
                } else {
                    return  '[#'.$option[0].']';
                }
                // no break
            case 'new':
                // 新着
                $t = preg_replace('/\((.+)\)/u', '', $body);
                $dt = Carbon::parse($t);

                return $char.'timestamp('.$dt->timestamp.')';
                break;
            case 'size':
                // サイズはrem単位に変換する。
                return $char.'size'.self::px2rem($option[0]);
                break;
            case 'epoch':
                // 時差を考慮した新着（Adv.）
                return $char.'timestamp('.$option[0].');';
                break;
            case 'hr':
                return  '----';
                break;
            case 'pre':
            case 'sh':
            case 'code':
                return  '```'.$option[0]."\n".$body."\n".'```';
                break;
            case 'attach':
            case 'attachref':
            case 'ref':
                $file = array_shift($option);   // 一番最初の配列にはファイル名が入る。
                if (isset($option[1]) && preg_match('/\[{2}([^\]{2}].+)?\]{2}/u', $option[1], $m)) {
                    // 古い形式の添付ファイル（#ref(ファイル名, [[ページ名]], ...);という形式
                    $file = $m[1].'/'.$file;
                }

                if (count($option) !== 0) {
                    return  '{{'.$file.'|'.implode(',', $option).'}}';
                } else {
                    return  '![]('.$file.')';
                }

                break;
            case 'ruby':
                // ルビはoptionとbodyを逆転させる　&ruby(ルビの内容){対象}; →　&ruby(対象){ルビの内容};
                // tooltipの仕様と合わせる。LaTeX互換。
                return 'ruby('.$body.'){'.$option[0].'}';
            case 'ls':
            case 'ls2':
            case 'ls3':
                return $char.'ls'.isset($option) ? '('.implode(',', $option).')' : '';
                break;
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
                return '/*'.'deprecated:'.$plugin.' param:'.implode(',', $option).' body:'.$body.'*/';
                break;
        }
        if (preg_match('/\n/', $body)) {
            $body = '{'."\n".$body."\n".'}';
        }

        return $char.$plugin.
            (!empty($option) ? '('.implode(',', $option).')' : '').
            (!empty($body) ? '{'.$body.'}' : '');
    }

    /**
     * pxをremに変換.
     *
     * @param int $px ピクセル
     *
     * @return string
     */
    private static function px2rem(int $px)
    {
        return round($px / 16, 5);
    }

    /**
     * 添付ファイルディレクトリの処理
     */
    public function attach(){
        foreach (Storage::files($this->attach_dir) as &$file) {
            $a = $this->processAttach($file);

            if (empty($a)) continue;

            $ret[] = $a;
        }
    }
    /**
     * 添付ファイルを変換.
     *
     * @param string $file ファイル
     *
     * @return array
     */
    private function processAttach(string $file)
    {
        $count = 0;
        $locked = false;
        // 添付ファイルの名前を取得（かなりいい加減な正規表現だが・・・）
        // [ページ名]_[ファイル名].[バックアップ世代]という形式。
        // 添付するファイル名に制約がかかるため、LukiWikiではDBで管理する。
        //if (preg_match('/^(\w+)_(\w+)[\.]?(\d+|log)?$/', $file, $matches) === FALSE){
        
        if (!preg_match('/^(\w+)_(\w+)(\.\w+)?$/', pathinfo($file, PATHINFO_FILENAME), $matches)) {
            return;
        }
        
        if (isset($matches[3])) return;
        if (!empty(substr($file, strrpos($file, '.') + 1))) return;

        $page = hex2bin($matches[1]);

        // ページが存在しない場合、移行はしない。（IDで管理するため）
        $page = preg_replace('/\:/', '_', $page);
        $page_id = Page::where('name', $page)->pluck('id')->first();
        if (!$page_id) {
            return;
        }

        $original_name = hex2bin($matches[2]);

        if (Attachment::where(['page_id'=>$page_id, 'name' => $original_name])->exists()){
            // すでにデーターベースに登録されている場合スキップ
            return;
        }

        // 添付ファイルのバックアップは移行しない
        //if (!empty($matches[3]) {
        //    if ($matches[3] === 'log'){
        //        $count = (int) file_get_contents($file);
        //    }else{
        //        $backup_no = (int) $matches[3];
        //    }
        //}

        // 拡張子を取得
        $ext = substr($original_name, strrpos($original_name, '.') + 1);

        // 閲覧回数を取得
        $count = 0;
        if (Storage::exists($file.'.log')) {
            $log = explode("\n", trim(Storage::get($file.'.log')));
            $count = (int)explode(',', array_unshift($log))[0];
            $locked = $count !== 1 && array_shift($log) === '1';
        }

        // サーバーに保存する実際のファイル名は40文字のランダムの文字列＋拡張子
        $stored_name = Str::random(40).'.'.$ext;

        $dest = Config::get('lukiwiki.directory.attach').'/'.$stored_name;

        // LukiWikiの添付ディレクトリにコピー
        Storage::copy($file, $dest);

        try {
            $created = Carbon::createFromTimestamp(filectime($file));
        } catch (\Exception $e) {
            //dd($e);
            $created = null;
        }

        $r = [
           'page_id'     => $page_id,
           'name'        => $original_name,
           'count'       => $count,
           'locked'      => $locked,
           'stored_name' => $stored_name,
           'mime'        => Storage::mimeType($dest),
           'hash'        => hash_file('sha256', $file),
           'size'        => Storage::size($file),
           'meta'        => Storage::getMetaData($dest),
           'created_at'  => $created,
           'updated_at'  => Carbon::createFromTimestamp(Storage::lastModified($file)),
        ];
        dd($r);
    }

    public function processCounter(string $file)
    {
        // ページ名
        $page = hex2bin(substr($file, 0, strrpos($file, '.')));
        if (empty($page)) {
            // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
            return;
        }

        // :configから始まるページ名はPukiWikiのプラグインの初期設定で使う。
        // この値は使用しないため移行しない
        if (substr($page, 0, 7) === ':config') {
            return;
        }
        // :が含まれるページ名は_に変更する。
        $page = preg_replace('/\:/', '_', $page);

        $data = Storage::get($this->counter_dir.$file.'.count');

        list($total, $date, $today, $yesterday, $ip) = explode("\n", $data);
    }

    /**
     * ページの存在チェック.
     *
     * @param string $page
     *
     * @return bool
     */
    private static function exsists(string $page)
    {
        return Page::where('page', $page)->exists();
    }

    public function processBackup()
    {
    }
}
