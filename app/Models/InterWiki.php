<?php

/**
 * InterWikiモデル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Models;

use App\Enums\InterWikiType;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;

class InterWiki extends Model
{
    use Uuid;

    protected $guarded = ['id'];

    protected $table = 'interwikis';

    /**
     * InterWikiNameを取得.
     *
     * @param  string  $anchor
     * @return null|string
     */
    public static function getInterWikiName(string $anchor): ?string
    {
        [$name, $param] = explode(':', $anchor);

        $interwiki = self::where('name', $name)->where('type', InterWikiType::InterWikiName->value)->first();

        if (! $interwiki) {
            return null;
        }

        if (empty($param)) {
            return $interwiki->value;
        }

        // Encoding
        switch ($interwiki->encode) {
            case '':    // FALLTHROUGH
            case 'std': // Simply URL-encode the string, whose base encoding is the internal-encoding
                $param = rawurlencode($param);
                break;
            case 'asis': // FALLTHROUGH
            case 'raw': // Truly as-is
                break;
            case 'yw': // YukiWiki
                $param = mb_convert_encoding($param, 'SJIS', 'UTF-8');
                break;
            case 'moin': // MoinMoin
                $param = str_replace('%', '_', rawurlencode($param));
                break;
            default:
                // Encoding conversion into specified encode, and URLencode
                $param = rawurlencode(mb_convert_encoding($param, $interwiki->encode, 'UTF-8'));
        }

        // Replace or Add the parameter
        return (strpos($interwiki->value, '$1') !== false) ? str_replace('$1', $param, $interwiki->value) : $interwiki->value.$param;
    }
}
