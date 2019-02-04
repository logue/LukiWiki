<?php
/**
 * ページモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Carbon\Carbon;
use Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Intl\Collator\Collator;

class Page extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    const PAGELIST_CACHE = 'pages';
    const PAGELIST_CACHE_DATE = 'pages-modified';

    /**
     * ページに貼り付けられた添付ファイル.
     *
     * @return HasMany
     */
    public function attachements(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * ページのバックアップ.
     *
     * @return HasMany
     */
    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    /**
     * 新着記事を取得.
     *
     * @param int $limit
     *
     * @return object
     */
    public static function getLatest(int $limit = 20)
    {
        return self::select('id', 'name', 'description', 'updated_at')->orderBy('updated_at', 'desc')->limit($limit)->get();
    }

    /**
     * 全ページを取得.
     *
     * @return array
     */
    public static function getEntries(): array
    {
        $lastmod = self::lastModified();
        if ($lastmod->timestamp > (int) Cache::get(self::PAGELIST_CACHE_DATE)) {
            // DBの更新日時がキャッシュ生成日時よりも新しい場合、キャッシュを削除
            Cache::forget(self::PAGELIST_CACHE);
            Cache::forget(self::PAGELIST_CACHE_DATE);
        }
        $pages = self::pluck('name')->toArray();
        $collator = new Collator(Config::get('locale'));
        $collator->asort($pages, Collator::SORT_STRING);

        Cache::put(self::PAGELIST_CACHE, $pages);
        Cache::put(self::PAGELIST_CACHE_DATE, time());

        return $pages;
    }

    /**
     * ページの存在チェック.
     *
     * @param string $page
     *
     * @return bool
     */
    public static function exsists(string $page):boolean
    {
        return self::where('page', $page)->exists();
    }

    /**
     * 最新更新日.
     *
     * @param string $name ページ名
     *
     * @return Carbon
     */
    public static function lastModified(?string $name = null) : Carbon
    {
        return Carbon::parse(empty($name) ?
            self::select('updated_at')->max('updated_at') :
            self::select('updated_at')->where('name', $name)
        );
    }
}
