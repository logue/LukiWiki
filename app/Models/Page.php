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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use RegexpTrie\RegexpTrie;

class Page extends Model
{
    use SoftDeletes;

    private const PAGELIST_TRIE_CACHE = 'page_trie';

    private const PAGELIST_CACHE = 'pages';

    protected $guarded = ['id'];

    protected $casts = [
        'name' => 'string',
        'locked' => 'bool',
        'status' => 'int',
        'source' => 'string',
    ];

    /**
     * ページに貼り付けられた添付ファイル.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * ページのバックアップ.
     */
    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    /**
     * このページの所有者（未使用）.
     */
    public function user(): hasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * ページのカウンター
     */
    public function counter(): HasOne
    {
        return $this->hasOne(Counter::class);
    }

    /**
     * 検索.
     */
    public static function search(array $keywords): Builder
    {
        $query = self::select('name');
        foreach ($keywords as $keyword) {
            if (Config::get('database.default') === 'mysql') {
                $query
                    ->where('name', 'like', '%'.$keyword.'%')
                    ->orWhereRaw('match(`source`) against (? IN NATURAL LANGUAGE MODE)', [$keyword]);
            } else {
                $query
                    ->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('source', 'like', '%'.$keyword.'%');
            }
        }

        return $query;
    }

    /**
     * 新着記事を取得.
     *
     * @param  int  $limit  制限数
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getLatest(int $limit = 20): Builder
    {
        return self::select('id', 'name', 'description', 'updated_at')->orderBy('updated_at', 'desc')->limit($limit);
    }

    /**
     * 全ページを取得.
     */
    public static function getEntries(): array
    {
        return Cache::rememberForever(self::PAGELIST_CACHE, function () {
            $pages = [];

            // ページ名と更新日時を取得
            $data = self::pluck('updated_at', 'name')->all();
            $entries = array_keys($data);

            // ページ名でソート
            $collator = new \Collator(Config::get('locale') ?? 'en');
            $collator->asort($entries, \Collator::SORT_STRING);

            // ページ名と更新日時をマージする
            foreach ($entries as $entry) {
                $pages[$entry] = $data[$entry];
            }

            return $pages;
        });
    }

    /**
     * 自動リンク用トライ木を生成.
     */
    public static function getTrie(): ?string
    {
        return Cache::rememberForever(self::PAGELIST_TRIE_CACHE, function () {
            return RegexpTrie::union(array_keys(self::getEntries()))->build();
        });
    }

    /**
     * 最新更新日.
     *
     * @param  string  $name  ページ名
     */
    public static function lastModified(?string $name = null): Carbon
    {
        return Carbon::parse(empty($name) ?
            self::select('updated_at')->max('updated_at') :
            self::select('updated_at')->where('name', $name));
    }

    /**
     * キャッシュ削除.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::PAGELIST_CACHE);
        Cache::forget(self::PAGELIST_TRIE_CACHE);
    }
}
