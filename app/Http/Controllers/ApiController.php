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
     */
    public function atom():Response
    {
        return response()
            ->view('api.atom', ['entries' => Page::getLatest(20)])
            ->header('Content-Type', ' application/xml; charset=UTF-8');
    }

    /**
     * Sitemap.xmlを出力.
     */
    public function sitemap():Response
    {
        return response()
            ->view('api.sitemap', ['entries' => Page::all()])
            ->header('Content-Type', ' application/xml; charset=UTF-8');
    }

    /**
     * 添付ファイルを出力.
     */
    public function attachment(Request $request, string $id):Response
    {
        $file = Attachment::where('attachments.id', $id)->first();

        return response(Storage::get('attachments/'.$file->stored_name))
            ->header('Content-Type', $file->mime)
            ->header('Content-length', $file->size)
            ->header('Last-Modified', $file->updated_at);
    }
}
