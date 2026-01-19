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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    protected $guarded = ['id'];

    /**
     * このバックアップの元ページ.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * このバックアップの作成者.
     */
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }
}
