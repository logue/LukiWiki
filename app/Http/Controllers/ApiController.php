<?php
/**
 * APIコントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    const DEFAULT_PATH = ':api';

    /**
     * Atomを出力.
     *
     * @retun Illuminate\Http\Response
     */
    public function atom():Response
    {
        return response()
            ->view('api.atom', [
                'entries'    => Page::getLatest(20)->get(),
                'updated_at' => Page::getLatest(1)->value('updated_at'),

            ])
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /**
     * Sitemap.xmlを出力.
     *
     * @retun Illuminate\Http\Response
     */
    public function sitemap():Response
    {
        return response()
            ->view('api.sitemap', ['entries' => Page::all()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * opensearch.xmlを出力.
     *
     * @retun Illuminate\Http\Response
     */
    public function opensearch():Response
    {
        return response()
            ->view('api.opensearch')
            ->header('Content-Type', 'application/opensearchdescription+xml; charset=UTF-8');
    }

    /**
     * 添付ファイルを出力.
     *
     * @retun Illuminate\Http\Response
     */
    public function attachment(Request $request, int $id):Response
    {
        $file = Attachment::select('stored_name')->where('attachments.id', $id)->first();

        return response(Storage::get('attachments/'.$file->stored_name))
            ->header('Content-Type', $file->mime)
            ->header('Content-length', $file->size)
            ->header('Last-Modified', $file->updated_at);
    }

    /**
     * 添付ファイル存在確認.
     */
    public function checkExsists(Request $request, string $name):Response
    {
    }
}
