<?php
/**
 * プラグイン抽象化基底クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki;

abstract class AbstractPlugin
{
    /** @var string プラグイン名 */
    protected $name;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->name = get_class($this);
    }

    /**
     * インヴォーク.
     */
    public function __invoke()
    {
    }

    /**
     * ブロック型出力.
     *
     * @param array $args 引数
     *
     * @return string
     */
    public function executeBlock($args = [])
    {
        return '<div class="card"><div class="card-header">#'.$this->name.'</div><div class="card-body">'.implode(',', $args).'</div></div>';
    }

    /**
     * インライン型出力.
     *
     * @param array $args 引数
     *
     * @return string
     */
    public function executeInline($args = [])
    {
        return '<span class="badge badge-pill badge-secondary">&amp;'.$this->name.'('.implode(',', $args).')'.'</span>';
    }

    /**
     * AMP用インライン型出力.
     *
     * @param array $args 引数
     *
     * @return string
     */
    public function executeAmpBlock($args = [])
    {
        return $this->executeBlock($args);
    }

    /**
     * AMP用にインライン型出力.
     *
     * @param
     */
    public function executeAmpInline($args = [])
    {
        return $this->executeInline($args);
    }
}
