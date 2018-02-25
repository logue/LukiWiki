<?php
/**
 * 位置決めクラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Rules;

class Alignment
{
    public static function block($align)
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
    }

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
    }
}
