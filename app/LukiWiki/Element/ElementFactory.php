<?php
/**
 * 要素ファクトリークラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

class ElementFactory
{
    public static function &factory($element, $root, $text)
    {
        if (empty($root)) {
            return self::inline($text);
        }
        switch ($element) {
            case 'DList':
                return self::dList($root, $text);
                break;
            case 'Table':
                return self::table($root, $text);
                break;
            case 'YTable':
                return self::yTable($root, $text);
                break;
            case 'Plugin':
                //return self::plugin($root, $text);
                break;
        }
        // 'InlineElement'
        return self::inline($text);
    }

    private static function &inline($text)
    {
        // Check the first letter of the line
        if (substr($text, 0, 1) === '~') {
            $ret = new Paragraph(' '.substr($text, 1));
        } else {
            $ret = new InlineElement($text);
        }

        return $ret;
    }

    private static function &dList($root, $text)
    {
        $out = explode('|', ltrim($text), 2);
        if (count($out) < 2) {
            $ret = self::inline($text);
        } else {
            $ret = new DList($out);
        }

        return $ret;
    }

    private static function &table(&$root, $text, $is_guiedit = false)
    {
        if (!preg_match('/^\|(.+)\|([hHfFcC]?)$/', $text, $out)) {
            $ret = self::inline($text, $is_guiedit);
        } else {
            $ret = new Table($out);
        }

        return $ret;
    }

    private static function &yTable(&$root, $text)
    {
        if ($text === ',') {
            $ret = self::inline($text, $is_guiedit);
        } else {
            $ret = new YTable(explode(',', substr($text, 1)));
        }

        return $ret;
    }

    private static function plugin(&$root, $text)
    {
        /*
        $matches = [];

        if (preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(\{*)/', $text, $matches) && PluginRenderer::hasPluginMethod($matches[1], 'convert')) {
            $len = strlen($matches[3]);
            $body = [];
            if (preg_match('/\{{'.$len.'}\s*\r(.*)\r\}{'.$len.'}/', $text, $body)) {
                // Seems multiline-enabled block plugin
                $matches[2] .= "\r".$body[1]."\r";
            }
        }

        return new BlockPlugin($matches);
        */

        //TODO:Plugin Support
        return '<p>Plugin</p>';
    }
}
