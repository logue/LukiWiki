<?php

/**
 * Naviプラグイン.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\BlockPluginInterface;
use App\LukiWiki\Utility\WikiUrl;
use App\Models\Page;

class Navi extends AbstractPlugin implements BlockPluginInterface
{
    public function block(): string
    {
        $ret = [];
        $reserve = false;
        if (\count($this->params) !== 0) {
            [$home, $reverse] = array_pad($this->params, 2, null);
            $home = WikiUrl::getFullname($home, $this->page);
            $is_home = $home === $this->page;
            if (! Page::exists($home)) {
                return $this->error('No such page: '.e($home));
            }
            if (! $is_home && preg_match('/^'.preg_quote($home, '/').'/', $this->page) === false) {
                return $this->error('Not a child page like: '.e($home.'/'.WikiUrl::stripRelativePath($this->page)));
            }
            $reverse = strtolower($reverse) === 'reverse';
            $ret['home'] = $home;
        } else {
            $ret['home'] = $this->page;
            $is_home = true;
        }

        $pages = array_unique(preg_grep('/^'.preg_quote($ret['home'], '/').'($|\/)/', array_keys(Page::getEntries())));

        if ($reverse) {
            $pages = array_reverse($pages);
        }

        $ret['prev'] = $home;
        $ret['next'] = current($pages);
        foreach ($pages as $index => $page) {
            if ($page === $this->page) {
                $next_key = $index + 1;
                if (\array_key_exists($next_key, $pages)) {
                    $ret['next'] = $pages[$next_key];
                }
                break;
            }
            $ret['prev'] = $page;
        }

        $pos = strrpos($this->page, '/');
        $up = null;
        if ($pos > 0) {
            $ret['up'] = substr($this->page, 0, $pos);
        }

        if (! empty($next)) {
            $ret['next'] = $next;
        }

        if ($is_home) {
            // Show contents
            $count = \count($pages);
            if ($count === 0) {
                return $this->error('You already view the result.');
            }
            if ($count === 1) {
                // Sentinel only: Show usage and warning;
                return $this->error('No child page like: '.e($home).'/Foo');
            }
        }
        //dd($ret);
        return view('plugin.navi', ['ret' => $ret]);
    }
}
