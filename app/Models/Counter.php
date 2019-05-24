<?php
/**
 * カウンターモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Counter extends Model
{
    const CREATED_AT = null;

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

    /**
     * カウンター加算.
     *
     * @param string $page_id ページID
     *
     * @return array
     */
    public static function countUp(int $page_id): array
    {
        $counter = self::where('page_id', '=', $page_id)->first();

        // 書き込むデータ
        $value = [
            'ip_address' => \Request::ip(),
            'today'      => $counter->today + 1,
            'total'      => $counter->total + 1,
            'updated_at' => Carbon::now()->toDateTimeString(),
            'yesterday'  => $counter->yesterday,
        ];

        //dd($value);

        // 最後のカウントからの経過日数
        $interval_day = $counter->updated_at->day - Carbon::now()->day;

        if ($counter->ip_address === $value['ip_address'] && $interval_day === 0) {
            // 最後にアクセスしたひとのIPと同じ場合加算しない。ただし１日以上離れていた場合は加算する
            return $counter->toArray();
        }

        //dd($interval_day);

        if ($interval_day >= 2) {
            // 前日にアクセスが無かった場合、前日のカウントを0にする。
            $value['yesterday'] = 0;
        }

        if ($interval_day === 1) {
            // それまでの本日のカウントを昨日のカウントに代入して、本日のカウントに1を代入
            $value = [
                'yesterday' => $counter->today,
                'today'     => 1,
            ];
        }

        self::updateOrCreate(['page_id' => $page_id], $value);

        return $value;
    }
}
