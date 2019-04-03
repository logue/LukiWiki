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

    /** @var array パラメータ */
    protected $params;

    /**
     * コンストラクタ
     */
    public function __construct(array $params)
    {
        $this->name = \get_class($this);
        $this->params = $params;
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
    public function executeBlock()
    {
        return '<div class="card"><div class="card-header">#'.$this->name.'</div><div class="card-body">'.implode(',', $this->params).'</div></div>';
    }

    /**
     * インライン型出力.
     *
     * @param array $args 引数
     *
     * @return string
     */
    public function executeInline()
    {
        return '<span class="badge badge-pill badge-secondary">&amp;'.$this->name.'('.implode(',', $this->params).')'.'</span>';
    }

    /**
     * AMP用インライン型出力.
     *
     * @param array $args 引数
     *
     * @return string
     */
    public function executeAmpBlock()
    {
        return $this->executeBlock();
    }

    /**
     * AMP用にインライン型出力.
     *
     * @param
     */
    public function executeAmpInline()
    {
        return $this->executeInline();
    }
}
