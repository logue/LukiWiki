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

    /**
     * この添付ファイルの貼り付けられたページ.
     */
    public function page()
    {
        return $this->belongsTo('App\Models\Page');
    }
}
