<?php

/**
 * 位置決めクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Rules;

class Alignment
{
    /**
     * ブロック型.
     *
     * @param  string  $align
     * @return string
     */
    public static function block(string $align)
    {
        switch (strtolower($align)) {
            case 'left':
                // ←
                return 'ml-0 mr-auto';
            case 'right':
                // →
                return 'ml-auto mr-0';
            case 'center':
                // 中央
                return 'mx-auto';
            case 'justify':
                // 両端寄せ
                return 'mx-0';
            case 'top':
                // ↑
                return 'mt-0 mb-auto';
            case 'middle':
                // 上下中央
                return 'my-auto';
            case 'bottom':
                // ↓
                return 'mt-auto mb-0';
        }

        return '';
    }

    /**
     * インライン型.
     *
     * @param  string  $align
     * @return string
     */
    public static function inline($align)
    {
        switch (strtolower($align)) {
            case 'left':
                // ←
                return 'text-left';
            case 'right':
                // →
                return 'text-right';
            case 'center':
                // 中央
                return 'text-center';
            case 'justify':
                // 両端寄せ
                return 'text-justify';
            case 'baseline':
                return 'align-baseline';
            case 'top':
                // ↑
                return 'align-top';
            case 'middle':
                // 上下中央
                return 'align-middle';
            case 'bottom':
                // ↓
                return 'align-bottom';
        }

        return '';
    }

    /**
     * フレックスボックス型.
     *
     * @param  string  $align
     * @return string
     */
    public static function flex($align)
    {
        switch (strtolower($align)) {
            case 'left':
                // ←
                return 'justify-content-start';
            case 'right':
                // →
                return 'justify-content-end';
            case 'center':
                // 中央
                return 'justify-content-center';
            case 'justify':
            case 'between':
                // 両端寄せ
                return 'justify-content-between';
            case 'around':
                return 'justify-content-around';
            case 'top':
                // ↑
                return 'align-items-start';
            case 'middle':
                // 上下中央
                return 'align-items-center';
            case 'baseline':
                return 'align-items-baseline';
            case 'bottom':
                // ↓
                return 'align-items-end';
            case 'stretch':
                return 'align-items-stretch';
        }

        return '';
    }

    /**
     * 回り込み解除.
     *
     * @return string
     */
    public static function clear()
    {
        return '<div class="clearfix"></div>';
    }
}
