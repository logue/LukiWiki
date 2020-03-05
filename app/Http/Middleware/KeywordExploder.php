<?php

/**
 * キーワードを配列化.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue All Rights Reserved
 * @license   MIT
 */

namespace App\Http\Middleware;

use Closure;

class KeywordExploder
{
    /**
     * キーワードを空白文字で分割し配列にする。
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $keyword = $request->input('keyword');

        if (!empty($keyword)) {
            // +を半角スペースに変換（GETメソッド対策）
            $keyword = str_replace('+', ' ', $keyword);
            // 全角スペースを半角スペースに変換
            $keyword = str_replace('　', ' ', $keyword);
            // 全角スペースを半角スペースに変換
            $keyword = str_replace('%', ' ', $keyword);
            // 取得したキーワードのスペースの重複を除く。
            $keyword = preg_replace('/\s(?=\s)/', '', $keyword);
            // キーワード文字列の前後のスペースを削除する
            $keyword = trim($keyword);
            // 整形の結果、空白しか残らなかった場合nullを返す（スペースしか入力されていないなど）
            if (empty($keyword) || $keyword === '') {
                return;
            }
            // 半角カナを全角カナへ変換
            $keyword = mb_convert_kana($keyword, 'KV');
            // 半角スペースで配列にし、重複は削除する
            $ret['keyword'] = array_unique(explode(' ', $keyword));
        }

        $request->merge($ret);

        return $next($request);
    }
}
