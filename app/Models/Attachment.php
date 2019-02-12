<?php
/**
 * 添付ファイルモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'json',
    ];

    /**
     * この添付ファイルの貼り付けられたページ.
     */
    public function page() : belongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * ファイルの存在チェック.
     *
     * @param string $page
     * @param string $file
     *
     * @return bool
     */
    public static function exsists(string $page, string $file):boolean
    {
        // TODO:
    }
}
