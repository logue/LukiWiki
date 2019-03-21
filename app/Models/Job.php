<?php
/**
 * ジョブモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload'      => 'json',
        'attempts'     => 'int',
        'reserved_at'  => 'int',
        'available_at' => 'int',
        'created_at'   => 'int',
    ];
}
