<?php
/**
 * ページモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    /**
     * ページに貼り付けられた添付ファイル.
     */
    public function attachements()
    {
        return $this->hasMany('App\Models\Attachment');
    }

    /**
     * ページのバックアップ.
     */
    public function backups()
    {
        return $this->hasMany('App\Models\Backup');
    }
}
