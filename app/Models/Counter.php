<?php

/**
 * カウンターモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Counter extends Model
{
    public const CREATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'total'     => 'int',
        'today'     => 'int',
        'yesterday' => 'int',
    ];

    /**
     * このカウンターの元ページ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public static function today(): Builder
    {
        return self::whereRaw('DATE(updated_at) = DATE(NOW())');
    }
}
