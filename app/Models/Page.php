<?php
/**
 * ページモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Intl\Collator\Collator;
use Config;

class Page extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    /**
     * ページに貼り付けられた添付ファイル.
     */
    public function attachements()
    {
        return $this->hasMany('App\Models\Attachment');
    }

    /**
     * ページのバックアップ.
     */
    public function backups()
    {
        return $this->hasMany('App\Models\Backup');
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
     */
    public static function getEntries()
    {
        $pages = self::pluck('name')->toArray();
        $collator = new Collator(Config::get('locale'));
        $collator->asort($pages, Collator::SORT_STRING);
        return $pages;
    }
}
