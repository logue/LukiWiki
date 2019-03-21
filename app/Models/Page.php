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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use RegexpTrie\RegexpTrie;
use Symfony\Component\Intl\Collator\Collator;

class Page extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'locked' => 'bool',
        'status' => 'int',
    ];

    const PAGELIST_TRIE_CACHE = 'page_trie';
    const PAGELIST_CACHE = 'pages';

    /**
     * 保存時にキャッシュ削除.
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     *
     * @return Illuminate\Database\Eloquent\Builder
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
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * ページのバックアップ.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    /**
     * このページの所有者.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->hasOne(User::class);
    }

    /**
     * ページのカウンター
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function counter(): HasOne
    {
        return $this->hasOne(Counter::class);
    }

    /**
     * 検索.
     *
     * @param array $keywords
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public static function search(array $keywords):Builder
    {
        $query = self::select('name');
        foreach ($keywords as $keyword) {
            if (\Config::get('database.default') === 'mysql') {
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
     * ページ名からIDを取得.
     *
     * @param string $page
     *
     * @return int
     */
    public static function getId(string $page):?int
    {
        return self::where('name', '=', $page)->value('id');
    }

    /**
     * ページに貼り付けられた添付ファイル一覧.
     *
     * @param string $page
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public static function getAttachments(string $page):Builder
    {
        return self::where('pages.name', $page)
            ->join('attachments', 'pages.id', '=', 'attachments.page_id');
    }

    /**
     * ページのバックアップ一覧.
     *
     * @param string $page
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public static function getBackups(string $page):Builder
    {
        return self::where('pages.name', $page)
            ->join('backups', 'pages.id', '=', 'backups.page_id');
    }

    /**
     * ページのカウンターを取得.
     *
     * @param string $page
     *
     * @@return Illuminate\Database\Eloquent\Builder
     */
    public static function getCounter(string $page):Builder
    {
        return self::where('pages.name', $page)
            ->join('counters', 'pages.id', '=', 'counters.page_id');
    }

    /**
     * 新着記事を取得.
     *
     * @param int $limit
     *
     * @return Illuminate\Database\Eloquent\Builder
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
            // ページ名と更新日時を取得
            $data = self::pluck('updated_at', 'name')->all();
            $entries = array_keys($data);

            // ページ名でソート
            $collator = new Collator(Config::get('locale'));
            $collator->asort($entries, Collator::SORT_STRING);

            // ページ名と更新日時をマージする
            foreach ($entries as $entry) {
                $pages[$entry] = $data[$entry];
            }

            return $pages;
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
    public static function exists(string $page):bool
    {
        return self::where('name', $page)->exists();
    }

    /**
     * 最新更新日.
     *
     * @param string $name ページ名
     *
     * @return Carbon\Carbon
     */
    public static function lastModified(?string $name = null) : Carbon
    {
        return Carbon::parse(empty($name) ?
            self::select('updated_at')->max('updated_at') :
            self::select('updated_at')->where('name', $name)
        );
    }

    /**
     * カウンター加算.
     *
     * @param string $page
     *
     * @return void
     */
    public static function countUp(string $page): void
    {
        $counter = self::getCounter($page)->latest()->first();

        $value = [
            'ip_address' => \Request::ip(),
            'today'      => $counter->today++,
            'total'      => $counter->total++,
            'updated_at' => Carbon::now()->toDateTimeString(),
            'yesterday'  => $counter->yesterday,
        ];

        // 通常のカウントアップ
        if ($counter->ip_address === $value['ip_address']) {
            // 最後にアクセスしたひとのIPと同じ場合加算しない
            return;
        }

        if (($counter->updated_at->day - Carbon::now()->day) === 1) {
            // 日付変更があった場合、本日のカウントを昨日のカウントに上書きして1を代入
            $value = [
                'yesterday' => $counter->today,
                'today'     => 1,
            ];
        } elseif (($counter->updated_at->day - Carbon::now()->day) >= 2) {
            // 日付の差分が２日以上（前日にアクセスが無かった）の場合
            $value['yesterday'] = 0;
        }

        Counter::updateOrCreate(['page_id' => $counter->page_id], $value);
    }
}
