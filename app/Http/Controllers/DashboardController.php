<?php
/**
 * ダッシュボード面コントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\InterWiki;
use App\Models\Page;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** @var \Illuminate\Database\Eloquent\Model $page ページモデル */
    protected $page;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // 認証用ミドルウェア
        //$this->middleware('auth');
    }

    /**
     * 管理トップページ.
     *
     * @return \Illuminate\View\View
     */
    public function __invoke(): View
    {
        return view('dashboard/index', ['title'=>'Administrator', 'counter' => new Counter()]);
    }

    /**
     * ユーザ一覧.
     *
     * @return \Illuminate\View\View
     */
    public function user(Request $request)
    {
        $users = User::paginate(15);

        return view('dashboard/users', ['title'=>'User List']);
    }

    /**
     * WikiデータをLukiWiki形式に変換.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function convert(Request $request)
    {
        if ($request->isMethod('post')) {
            $path = $request->input('path');

            if (\Storage::exists($path)) {
                $errors = [];
                switch ($request->input('type')) {
                    case 'attach':
                        $request->session()->flash('message', '添付ファイルのインポートのキューを実行しました。');
                        foreach (Storage::files($path.'/attach/') as $file) {
                            try {
                                $this->dispatch(new \App\Jobs\ProcessAttachmentData($file));
                            } catch (\Exception $e) {
                                $errors[] = $file;
                            }
                        }
                        break;
                    case 'backup':
                        $request->session()->flash('message', 'バックアップのインポートのキューを実行しました。');
                        foreach (Storage::files($path.'/backup/') as $file) {
                            try {
                                $this->dispatch(new \App\Jobs\ProcessBackupData($file));
                            } catch (\Exception $e) {
                                $errors[] = $file;
                            }
                        }
                        break;
                    case 'counter':
                        $request->session()->flash('message', 'カウンターのインポートのキューを実行しました。');
                        foreach (Storage::files($path.'/counter/') as $file) {
                            try {
                                $this->dispatch(new \App\Jobs\ProcessCounterData($file));
                            } catch (\Exception $e) {
                                $errors[] = $file;
                            }
                        }
                        break;
                    case 'wiki':
                        $request->session()->flash('message', 'Wikiデータのインポートのキューを実行しました。');
                        foreach (Storage::files($path.'/wiki/') as $file) {
                            try {
                                $this->dispatch(new \App\Jobs\ProcessWikiData($file));
                            } catch (\Exception $e) {
                                $errors[] = $file;
                            }
                        }
                        break;
                    default:
                        $request->session()->flash('message', 'キューの実行をキャンセルしました。');
                }
                if (\count($errors) !== 0) {
                    $request->session()->flash('message', '以下のファイルでエラーが発生しました。：'."\n".implode("\n", $errors));
                }
            } else {
                $request->session()->flash('message', 'ディレクトリが見つかりません。');
            }

            if ($request->input('type') === 'test') {
                //$request->session()->flash('message', 'テスト実行');
                //$testfile = 'main/wiki/E3839EE38393E3838EE382AE2F4D4D4C2F4C6F6E646F6E646572727920416972.txt';
                //dd(Storage::exists($testfile), Storage::path($testfile));
                //$f = str_replace('/', DIRECTORY_SEPARATOR, Storage::path($testfile));
                //dd($f);
                // http://localhost:8000/%E3%83%9E%E3%83%93%E3%83%8E%E3%82%AE/MML/Londonderry%20Air
                //$this->dispatch(new \App\Jobs\ProcessWikiData($testfile));
            }

            return redirect(':dashboard/convert');
        }

        return view('dashboard/convert', ['title'=>'Convert PukiWiki data.']);
    }

    /**
     * キャッシュクリア.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function clearCache(Request $request)
    {
        $args = $request->input('cache');
        $msg = [];

        if (\count($args) === 0) {
            $request->session()->flash('message', '何も選択されていないため処理を中断しました。');

            return redirect(self::DEFAULT_PATH);
        }

        foreach ($args as $cache) {
            switch ($cache) {
                case 'view':
                    $files = glob(storage_path('framework/views').'/*.php');
                    foreach ($files as $no=>$file) {
                        unlink($file);
                    }
                    $msg[] = 'ビューキャッシュ';
                    break;
                case 'debug':
                    $files = glob(storage_path('framework/debugbar').'/*.json');
                    $msg[] = 'デバッグ情報';
                    break;
                case 'system':
                    Cache::flush();
                    $msg[] = 'システムキャッシュ';
                    break;
            }
        }

        $request->session()->flash('message', '以下のキャッシュを削除しました：<br />'.implode("<br />\n", $msg));

        return redirect(self::DEFAULT_PATH);
    }

    /**
     * InterWiki登録.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function interwiki(Request $request): View
    {
        $interwiki = new InterWiki();

        if ($request->isMethod('post')) {
            // 編集処理
            // TODO: バリデーション
            $value = [
                'name'   => $request->input('name'),
                'value'  => $request->input('value'),
                'type'   => $request->input('type'),
                'encode' => $request->input('encode'),
            ];
            switch ($request->input('action')) {
                case 'create':
                    $interwiki->create($value);
                    $request->session()->flash('message', 'InterWikiを作成しました。');
                    break;
                case 'update':
                    $interwiki->update($value)->where('id', '=', $request->input('id'));
                    $request->session()->flash('message', 'InterWikiを保存しました。');
                    break;
                case 'delete':
                    $interwiki->destroy($request->input('id'));
                    $request->session()->flash('message', 'InterWikiを削除しました。');
                    break;
            }
        }

        return view('dashboard/interwiki', [
            'title'  => 'InterWikiName.',
            'entries'=> $interwiki->paginate(20),
        ]);
    }

    /**
     * CAPTCHAの動作チェック.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function captchaTest(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = ['captcha' => 'required|captcha'];
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                echo '<p style="color: #dc3545;">Incorrect!</p>';
            } else {
                echo '<p style="color: #28a745;">Matched :)</p>';
            }
        }

        $form = '<form method="post" action="captcha-test">';
        $form .= '<input type="hidden" name="_token" value="'.csrf_token().'">';
        $form .= '<p>'.captcha_img().'</p>';
        $form .= '<p><input type="text" name="captcha" class=""/></p>';
        $form .= '<p><button type="submit" name="check">Check</button></p>';
        $form .= '</form>';

        return $form;
    }
}
