<?php
/**
 * ページモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Intl\Collator\Collator;

class Page extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

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
    public static function getEntries():array
    {
        $pages = self::pluck('name')->toArray();
        $collator = new Collator(Config::get('locale'));
        $collator->asort($pages, Collator::SORT_STRING);

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
}
