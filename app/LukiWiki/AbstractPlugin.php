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

    /** @var PluginType タイプ */
    protected $type;

    /** @var array パラメータ */
    protected $params;

    /** @var string 本文 */
    protected $body;

    /** @var string ページ名 */
    protected $page;

    /** @var array メタ情報 */
    protected $meta = [];

    /**
     * コンストラクタ
     */
    public function __construct(int $type, array $params, ?string $body, string $page)
    {
        $this->name = \get_class($this);
        $this->type = $type;
        $this->params = $params;
        $this->body = $body;
        $this->page = $page;
        Debugbar::startMeasure('plugin', 'Process '.$this->name.' plugin.');
        $this->init();
    }

    public function __destruct()
    {
        Debugbar::stopMeasure('plugin');
    }

    /**
     * プラグインを出力.
     */
    public function __toString()
    {
        if ($this->type === PluginType::Block) {
            return $this->block();
        }
         if ($this->type === PluginType::Inline) {
            return $this->inline();
        }

        // AMPの処理とか

        return $this->message(__('Not implimented.'), 'secondary');
    }

    /**
     * 共通処理.
     */
    public function init(): void
    {
        // parrent::construct();を各プラグイン内に入れるのもいいが、第三者が作るときに紛らわしくなるので
    }

    /**
     * メタ情報取得.
     *
     * @return array
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
    public function syntax(): array
    {
        return [
            '@'.$this->name.'(param1[,param2, param3 ...]){body};',
            '&amp;'.$this->name.'(param1[,param2, param3 ...]){body};',
        ];
    }

    /**
     * ブロック型出力.
     *
     * @param array $args 引数
     *
     * @return string
     */
    public function block(): string
    {
        return $this->message(__('Not implimented.'), 'secondary');
    }

    /**
     * インライン型出力.
     *
     * @param array $args 引数
     *
     * @return string
     */
    public function inline(): string
    {
        return $this->message(__('Not implimented.'), 'secondary');
    }

    /**
     * メッセージ.
     *
     * @param string $message
     * @param string $message_type
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
     * @param string $message
     *
     * @return string
     */
    public function error($message): string
    {
        return $this->message($message, 'warning');
    }
}
