<?php

namespace App\Http\Controllers;

use App\LukiWiki;

class WikiController extends Controller
{
    /**
     * Wikiを表示.
     *
     * @param string $page
     *
     * @return Response
     */
    public function __invoke($page = null)
    {
        $wiki_content = '+test';

        $obj = LukiWiki\Parser::factory($wiki_content);

        return view(
           'base',
           [
               'content' => $obj,
               'title'   => 'test',
           ]
        );
    }
}
