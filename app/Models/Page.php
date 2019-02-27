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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use RegexpTrie\RegexpTrie;
use Symfony\Component\Intl\Collator\Collator;

class Page extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    const PAGELIST_TRIE_CACHE = 'page_trie';
    const PAGELIST_CACHE = 'pages';

    /**
     * 保存時にキャッシュ削除.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    protected function setKeysForSaveQuery(Builder $query):Builder
    {
        Cache::forget(self::PAGELIST_CACHE);
        Cache::forget(self::PAGELIST_TRIE_CACHE);

        return parent::setKeysForSaveQuery($query);
    }

    /**
     * ページに貼り付けられた添付ファイル.
     *
     * @return HasMany
     */
    public function attachments(): HasMany
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
     * ページ名からIDを取得.
     *
     * @param string $page
     *
     * @return int
     */
    public static function getPageId(string $page):?int
    {
        return self::where('name', '=', $page)->value('id');
    }

    /**
     * ページに貼り付けられた添付ファイル一覧.
     *
     * @param string $page
     *
     * @return Builder
     */
    public static function getAttachments(string $page):Builder
    {
        return self::where('pages.name', $page)
            ->join('attachments', 'pages.id', '=', 'attachments.page_id');
    }

    /**
     * 新着記事を取得.
     *
     * @param int $limit
     *
     * @return Builder
     */
    public static function getLatest(int $limit = 20):Builder
    {
        return self::select('id', 'name', 'description', 'updated_at')->orderBy('updated_at', 'desc')->limit($limit);
    }

    /**
     * 全ページを取得.
     *
     * @return array
     */
    public static function getEntries(): array
    {
        return Cache::rememberForever(self::PAGELIST_CACHE, function () {
            $pages = self::pluck('name', 'updated_at')->all();
            $collator = new Collator(Config::get('locale'));
            $collator->asort($pages, Collator::SORT_STRING);

            return array_flip($pages);
        });
    }

    /**
     * 自動リンク用トライ木を生成.
     *
     * @return string
     */
    public static function getTrie(): string
    {
        return Cache::rememberForever(self::PAGELIST_TRIE_CACHE, function () {
            return RegexpTrie::union(array_keys(self::getEntries()))->build();
        });
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
