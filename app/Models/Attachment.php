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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'size'   => 'int',
        'count'  => 'int',
        'locked' => 'bool',
        'meta'   => 'json',
    ];

    protected $touches = ['page'];

    /**
     * この添付ファイルの貼り付けられたページ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page(): belongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * この添付ファイルの投稿者.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }
}
