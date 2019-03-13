<?php
/**
 * 直リンク禁止ミドルウェア.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProhibitDirectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 参考：https://qiita.com/yukiyukki/items/c5895056a614371473af
        if (!$request->session()->has('refferal')) {
            return abort(403);
        }

        return $next($request);
    }
}
