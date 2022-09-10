<?php

/**
 * バックアップモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019,2022 Logue
 * @license   MIT
 */

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    use Uuid;

    protected $guarded = ['id'];

    /**
     * このバックアップの元ページ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * このバックアップの作成者.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }
}
