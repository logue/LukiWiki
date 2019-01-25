<?php
/**
 * PukiWiki形式をLukiWiki形式に変換.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Utility;

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class Converter
{
    /**
     * コンストラクタ
     *
     * @param string $path PukiWikiのデーターのディレクトリへのパス
     */
    public function __construct(string $path)
    {
        // mimeを保存するため、コンストラクト時に使用可能かをチェック
        if (class_exists('finfo') === false) {
            throw new \Exception('fileinfo is not installed. Please install fileinfo extention and modify php.ini first.');
        }

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
        foreach (Storage::files($this->wiki_dir) as &$file) {
            // ページ名
            $page = hex2bin(pathinfo($file, PATHINFO_FILENAME));

            if (empty($page)) {
                // ファイル名が存在しない場合スキップ（.gitignoreとかの隠しファイルも省ける）
                continue;
            }

            // :configから始まるページ名はPukiWikiのプラグインの初期設定で使う。
            // この値は使用しないため移行しない
            if (substr($page, 0, 7) === ':config' || substr($page, 0, 3) === ':log') {
                continue;
            }
            // :が含まれるページ名は_に変更する。
            $page = preg_replace('/\:/', '_', $page);
            $data = explode("\n", rtrim(Storage::get($file)));

            if ($page !== 'Web素材') {
                continue;
            }

            $ret = self::pukiwiki2lukiwiki($data);

            $source = implode("\n", $ret['data']);

            try {
                $created = filectime(storage_path($file));
            } catch (\Exception $e) {
                //dd($e);
                $created = null;
            }

            $buf[] = [
                'name'        => $page,
                'user_id'     => 0,
                'source'      => $source,
                'description' => $ret['description'] ?? mb_strimwidth($source, 0, 256, '...'),
                'locked'      => $ret['locked'],
                'status'      => 0,
                'ip'          => $_SERVER['REMOTE_ADDR'],
                'created_at'  => $created,
                'updated_at'  => Storage::lastModified($file),
            ];
        }
        dd($buf);
    }

    /**
     * PukiWiki文法をLukiWiki文法に変換.
     *
     * @param array $lines
     *
     * @return array
     */
    public static function pukiwiki2lukiwiki(array $lines)
    {
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

            // リンクの形式変更
            $line = preg_replace_callback('/\[{2}([^\]*].+)?\]{2}/u', function ($matches) {
                if (!isset($matches[1])) {
                    return '';
                }
                $tmp = explode(':', $matches[1]);

                if (preg_match('/^(.+)>(.+)$/u', $matches[1], $m) !== 0) {
                    // [[foo>bar]]、[[foo>bar:fiz]]、[[foo>http://aaa]]
                    return '['.$m[1].']('.$m[2].')';
                } elseif (count($tmp) > 2) {
                    // [[foo:bar]]、[[foo:http://aaa]]
                    return '['.array_shift($tmp).']('.implode(':', $tmp).')';
                } else {
                    return '['.$matches[1].']';
                }
            }, $line);

            // 打ち消し線
            $line = preg_replace('/%{2}(?!%)((?:(?!%{2}).)*)%{2}/u', '~~${1}~~', $line);
            // コード
            $line = preg_replace('/@{2}(?!@)((?:(?!@{2}).)*)@{2}/u', '`${1}`', $line);
            // イタリック
            $line = preg_replace('/\'{3}(?!%)((?:(?!\'{3}).)*)\'{3}/u', '** ${1} **', $line);
            // 強調
            $line = preg_replace('/\'{2}(?!%)((?:(?!\'{2}).)*)\'{2}/u', '* ${1} *', $line);

            // 添付ファイル
            $line = preg_replace_callback('/&(\w+)\(((?:(?!\)).)*)\)?(?:\{((?:(?!\)).)*)\})?;/u', function ($matches) {
                switch ($matches[1]) {
                        case 'new':
                            // 新着
                            $t = preg_replace('/\((.+)\)/u', '', $matches[3]);
                            $dt = Carbon::parse($t);

                            return '&timestamp('.$dt->timestamp.');';
                            break;
                        case 'epoch':
                            // 時差を考慮した新着（Adv.）
                            return '&timestamp('.$matches[2].');';
                            break;
                        case 'attach':
                        case 'ref':
                        case 'attachref':
                        case 'img':
                            $params = explode(',', $matches[2].(isset($matches[3]) ? (','.$matches[3]) : ''));
                            $file = array_shift($params);
                            if (isset($params[1]) && preg_match('/\[{2}([^\]{2}].+)?\]{2}/u', $params[1])) {
                                // 古い形式の添付ファイル
                                $file = $matches[1].'/'.$file;
                            }

                            return '{{'.$file.'|'.implode(',', $params).'}}';
                            break;
                    }

                return $matches[0];
            },
                $line
            );

            switch ($char) {
                // プラグインの変換処理
                case '#':
                    preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(?:\{\{*(.+?)\}\}*)?/', $line, $matches);
                    $plugin = trim($matches[1]);
                    $option = isset($matches[2]) ? trim($matches[2]) : null;
                    $body = isset($matches[3]) ? str_replace("\r", "\n", trim($matches[3])) : null;
                    //dd($line, $matches, $body);
                    switch ($plugin) {
                        case 'hr':
                            $ret[] = '----';
                            break;
                        case 'pre':
                        case 'sh':
                        case 'code':
                            $ret[] = '```'.$option."\n".$body."\n".'```';
                            break;
                        case 'attach':
                        case 'attachref':
                        case 'ref':
                            if (empty($body)) {
                                $ret[] = '{{'.$option.'}}';
                            } else {
                                $ret[] = '{{'.$option.'|'.$body.'}}';
                            }
                            break;
                        case 'freeze':
                            $freezed = true;
                            break;
                        case 'description':
                            $description = $option;
                            break;
                        default:
                            // ブロック型プラグインは#から@に変える
                            $ret[] = str_replace_first('#', '@', $line);
                            break;
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
                        $type = trim($matches[1]);
                        $text = trim($matches[2]);
                        $ret[] = str_repeat($type, $level).' '.$text;
                        $matches = [];
                    }
                    break;
                default:
                    // 他の行は右トリム
                    $ret[] = rtrim($line);
                    break;
            }
        }

        return ['data' => $ret, 'locked' => $freezed, 'description' => $description];
    }

    /**
     * 添付ファイルを変換.
     *
     * @param string $file ファイル
     *
     * @return array
     */
    public function processAttach(string $file)
    {
        // 添付ファイルの名前を取得（かなりいい加減な正規表現だが・・・）
        // [ページ名]_[ファイル名].[バックアップ世代]という形式。
        // 添付するファイル名に制約がかかるため、LukiWikiではDBで管理する。
        //if (preg_match('/^(\w+)_(\w+)[\.]?(\d+|log)?$/', $file, $matches) === FALSE){
        if (preg_match('/^(\w+)_(\w+)$/', $file, $matches) === false) {
            return;
        }

        $page = hex2bin($matches[1]);

        // ページが存在しない場合、移行はしない。（IDで管理するため）
        if (!in_array($page, $this->pages)) {
            return;
        }

        $filename = hex2bin($matches[2]);

        // 添付ファイルのバックアップは移行しない
        //if (!empty($matches[3]) {
        //    if ($matches[3] === 'log'){
        //        $count = (int) file_get_contents($file);
        //    }else{
        //        $backup_no = (int) $matches[3];
        //    }
        //}

        // 拡張子を取得
        $ext = substr($filename, strrpos($filename, '.') + 1);

        // 閲覧回数を取得
        $count = 0;
        if (file_exists($this->attachments_dir.'/'.$file.'.log')) {
            $count = (int) trim(file_get_contents($this->attachments_dir.'/'.$file.'.log'));
        }

        // サーバーに保存する実際のファイル名は40文字のランダムの文字列＋拡張子
        $realname = Str::random(40).$ext;

        // LukiWikiの添付ディレクトリにコピー
        Storage::copy($file, $this->attachments_dir.'/'.$realname);

        return [
           'page'        => $page,
           'name'        => $filename,
           'count'       => $count,
           'realname'    => $realname,
           'mime'        => finfo_file($finfo, $file),
           'hash'        => hash_file('sha256', $file),
           'size'        => filesize($file),
           'created_at'  => filectime($file),
           'updated_at'  => filemtime($file),
        ];
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
