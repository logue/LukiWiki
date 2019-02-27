<?php
/**
 * バックアップモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $guarded = ['id'];

    /**
     * このバックアップの元ページ.
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
