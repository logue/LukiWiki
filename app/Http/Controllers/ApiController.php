<?php
/**
 * APIコントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use App\Models\Page;

class ApiController extends Controller
{
    const DEFAULT_PATH = ':api';

    /**
     * Atomを出力.
     */
    public function atom()
    {
        return response()
            ->view('api.atom', ['entries' => Page::getLatest(20)])
            ->header('Content-Type', ' application/xml; charset=UTF-8');
    }

    /**
     * Sitemap.xmlを出力.
     */
    public function sitemap()
    {
        return response()
            ->view('api.sitemap', ['entries' => Page::all()])
            ->header('Content-Type', ' application/xml; charset=UTF-8');
    }
}
