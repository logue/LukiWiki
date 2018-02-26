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
        $ret = null;
        if (!empty($root)) {
            switch ($element) {
                case 'DList':
                    $out = explode('|', ltrim($text), 2);
                    if (!count($out) < 2) {
                        $ret = new DList($out);
                    }
                    break;
                case 'Table':
                    if (preg_match('/^\|(.+)\|([hHfFcC]?)$/', $text, $out)) {
                        $ret = new Table($out);
                    }
                    break;
                case 'YTable':
                    if ($text !== ',') {
                        $ret = new YTable(explode(',', substr($text, 1)));
                    }
                    break;
                case 'Plugin':
                    $matches = [];

                    if (preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(\{*)/', $text, $matches)) {
                        $len = strlen($matches[3]);
                        $body = [];
                        if (preg_match('/\{{'.$len.'}\s*\r(.*)\r\}{'.$len.'}/', $text, $body)) {
                            // Seems multiline-enabled block plugin
                            $matches[2] .= "\r".$body[1]."\r";
                        }
                    }

                    $ret = new BlockPlugin($matches);
                    break;
            }
        } else {
            // Check the first letter of the line
            if (substr($text, 0, 1) === '~') {
                $ret = new Paragraph(' '.substr($text, 1));
            } else {
                $ret = new InlineElement($text);
            }
        }

        return $ret;
    }
}
