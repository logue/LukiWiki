<?php
/**
 * APIコントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\Enums\InterWikiType;
use App\Enums\PluginType;
use App\Models\Attachment;
use App\Models\InterWiki;
use App\Models\Page;
use Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    /**
     * Atomを出力.
     *
     * @retun \Illuminate\Http\Response
     */
    public function atom(): Response
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
     * @retun \Illuminate\Http\Response
     */
    public function sitemap(): Response
    {
        return response()
            ->view('api.sitemap', ['entries' => Page::all()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * opensearch.xmlを出力.
     *
     * @retun \Illuminate\Http\Response
     */
    public function opensearch(): Response
    {
        return response()
            ->view('api.opensearch')
            ->header('Content-Type', 'application/opensearchdescription+xml; charset=UTF-8');
    }

    /**
     * 添付ファイルを出力.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $int
     *
     * @retun \Illuminate\Http\Response
     */
    public function attachment(Request $request, int $id): Response
    {
        $file = Attachment::select('stored_name')->where('attachments.id', $id)->first();

        return response(Storage::get('attachments/'.$file->stored_name))
            ->header('Content-Type', $file->mime)
            ->header('Content-length', $file->size)
            ->header('Last-Modified', $file->updated_at);
    }

    /**
     * ページ一覧を出力.
     *
     * @param string $prefix ページ名の前方一致条件
     *
     * @return \Illuminate\Http\Response
     */
    public function list(string $prefix): Response
    {
        return response(Page::where('name', 'like', $prefix.'%')->pluck('updated_at', 'name'));
    }

    /**
     * グロッサリーの内容を出力.
     *
     * @param string $term グロッサリー名
     *
     * @return \Illuminate\Http\Response
     */
    public function glossary(string $term): Response
    {
        $ret = InterWiki::where('name', $term)->where('type', InterWikiType::Glossary)->first();
        if (!$ret) {
            return abort(404);
        }

        return response($ret);
    }

    /**
     * プラグインのAPI出力.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $name    プラグイン名
     * @param null|string              $page    ページ名
     *
     * @retun \Illuminate\Http\Response
     */
    public function plugin(Request $request, string $name, ?string $page): Response
    {
        if (Config::has('lukiwiki.plugin.'.$name)) {
            $class = Config::get('lukiwiki.plugin.'.$name);
            $plugin = new $class(PluginType::Api, $request->input('params') ?? [], '', $page);

            return response($plugin->api());
        }

        return abort(501, __('Not implemented.'));
    }
}
