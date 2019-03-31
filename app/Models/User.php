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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    /**
     * ページに貼り付けられた添付ファイル.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * ページのバックアップ.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    /**
     * 作業履歴.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function pageActivity():HasOneThrough
    {
        return $this->hasOneThrough(Backup::class, Page::class);
    }

    /**
     * このページの所有者.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pages() : HasMany
    {
        return $this->hasMany(Page::class);
    }
}
