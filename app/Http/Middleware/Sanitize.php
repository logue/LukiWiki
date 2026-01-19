<?php

/**
 * 入力文字列のサニタイズ.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue All Rights Reserved
 * @license   MIT
 */

namespace App\Http\Middleware;

use Closure;

class Sanitize
{
    /**
     * 全角を含む空白文字を削除し、LF改行に統一。空白だった場合NULLにする。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $input = $request->all();

        $trimmed = [];

        foreach ($input as $key => $val) {
            // 最初に文字列の前後の空白をpreg_replaceで削除する。
            // 次に改行コードをLFに統一する。
            // 最後にtrimで前後の改行文字を削除する。
            if (\is_string($val)) {
                $processed = trim(
                    str_replace(
                        [\chr(0x0D).\chr(0x0A), \chr(0x0D), \chr(0x0A)],
                        "\n",
                        preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $val)
                    )
                );
            } else {
                $processed = $val;
            }
            // 空白が残った場合NULLを代入する。
            $trimmed[$key] = empty($processed) ? null : $processed;
        }

        $request->merge($trimmed);

        return $next($request);
    }
}
