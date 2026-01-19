<?php

/**
 * プラグイン抽象化基底クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki;

use App\Enums\PluginType;
use Debugbar;

abstract class AbstractPlugin
{
    /** @var string プラグイン名 */
    protected $name;

    /** @var int タイプ */
    protected $type;

    /** @var array パラメータ */
    protected $params;

    /** @var string 本文 */
    protected $body;

    /** @var string ページ名 */
    protected $page;

    /** @var array メタ情報 */
    protected $meta = [];

    /** @var bool APIを外部に公開するか */
    protected $external = false;

    /**
     * コンストラクタ
     *
     * @param  App\Enums\PluginType  $type  呼び出しタイプ
     * @param  array  $params  パラメータ
     * @param  null|string  $body  本文
     * @param  string  $page  ページ名
     */
    final public function __construct(int $type, array $params, ?string $body, string $page)
    {
        $this->name = \get_class($this);
        $this->type = $type;
        $this->params = $params;
        $this->body = $body;
        $this->page = $page;
        Debugbar::startMeasure('plugin', 'Process '.$this->name.' plugin.');
        $this->init();
    }

    /**
     * デストラクタ
     */
    final public function __destruct()
    {
        $this->finalize();
        Debugbar::stopMeasure('plugin');
    }

    /**
     * プラグインを出力.
     */
    final public function __toString()
    {
        if ($this->type === PluginType::Block) {
            return $this->block();
        }
        if ($this->type === PluginType::Inline) {
            return $this->inline();
        }
        if ($this->type === PluginType::Api) {
            // デバッグ用？
            return $this->api();
        }

        // AMPの処理とか

        return $this->message(__('Not implimented.'), 'secondary');
    }

    /**
     * APIによるアクセス.
     *
     * @return mixed
     */
    public function api()
    {
        return __('Not implimented.');
    }

    /**
     * 共通開始処理.
     */
    public function init(): void {}

    /**
     * 共通終了処理.
     */
    public function finalize(): void {}

    /**
     * メタ情報取得.
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * プラグインの使用方法.
     */
    public function usage(): string
    {
        return $this->message(__('This plugin does not have infomation.'), 'info');
    }

    /**
     * プラグインの使用方法（codemirrorの入力補完機能で使用します。）.
     */
    public function syntax(): string
    {
        return '(param1[,param2, param3 ...]){body};';
    }

    /**
     * ブロック型出力.
     *
     * @param  array  $args  引数
     */
    public function block(): string
    {
        return $this->message(__('Not implimented.'), 'secondary');
    }

    /**
     * インライン型出力.
     *
     * @param  array  $args  引数
     */
    public function inline(): string
    {
        return $this->message(__('Not implimented.'), 'secondary');
    }

    /**
     * メッセージ.
     */
    public function message(string $message, string $message_type = 'info'): string
    {
        if ($this->type === PluginType::Inline) {
            return '<span class="badge badge-'.$message_type.'">&amp;'.$this->name.': '.$message.'</span>';
        }

        return '<div class="alert alert-'.$message_type.'"><b>@'.$this->name.'</b> :'.$message.'</div>';
    }

    /**
     * エラーメッセージ.
     *
     * @param  string  $message
     */
    public function error($message): string
    {
        return $this->message($message, 'warning');
    }
}
