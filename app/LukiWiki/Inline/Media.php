<?php

/**
 * メディアアドレス変換クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Inline;

use App\LukiWiki\AbstractInline;

/**
 * Media.
 */
class Media extends AbstractInline
{
    protected $count = 4;

    public function __toString()
    {
        // メディアファイル
        if (\Config::get('lukiwiki.render.expand_external_media_file')) {
            // 拡張子を取得
            $ext = substr($this->name, strrpos($this->href, '.') + 1);
            /*
            if ($this->isAmp) {
                switch ($ext) {
                    case 'jpeg':
                    case 'jpg':
                    case 'gif':
                    case 'png':
                    case 'svg':
                    case 'svgz':
                    case 'webp':
                    case 'bmp':
                    case 'ico':
                        return '<amp-img src="'.$this->href.'" alt="'.self::processText($this->title).'" width="1" height="1" class="external-media"><div fallback>'.self::processText($this->alias).'</div></amp-img>';
                        break;
                    case 'mp4':
                    case 'ogm':
                    case 'webm':
                        return '<amp-video src="'.$this->href.'" controls width="1" height="1" class="external-media"><div fallback>'.self::processText($this->alias).'</div></amp-video>';
                        break;
                    case 'wav':
                    case 'ogg':
                    case 'm4a':
                    case 'mp3':
                        return '<amp-audio  src="'.$this->href.'" controls width="auto" height="50"><div fallback>'.self::processText($this->alias).'</div></amp-audio>';
                        break;
                }

                return '<a href="'.$this->href.'" title="'.$this->title.'">'.$this->alias.'</a>';
            }
            */

            return '<lw-media><a href="' . $this->href . '" title="' . $this->title . '" rel="attachment">' . $this->alias . '</a></lw-media>';
        }

        return parent::setAutoLink($this->alias, $this->href, $this->name);
    }

    public function getPattern(): string
    {
        // ![alt](URL or WikiName "title"){option}
        return
            '!' .                                            // Media link detector
                '(?:\[' .
                    '(.[^\]\)]+)?' .                         // [1] alias
                '\])' .
                '(?:' .
                    '\(' .
                       '(.[^\(\)\[\]]+?)' .                  // [2] URL or WikiName and Filename
                       '(?:\s+(?:"(.*[^\(\)\[\]"]?)"))?' .   // [3] Title
                    '\)' .
                ')' .
                '(?:\{' .
                    '(.*[^\}])' .                            // [4] Body (option)
                '\})?';
    }

    public function setPattern(array $arr): void
    {
        //dd($this->getPattern(), $this->splice($arr));
        list($this->alias, $this->href, $this->title, $this->body) = $this->splice($arr);

        if (empty($this->alias)) {
            $this->alias = $this->href;
        }
        if (empty($this->title)) {
            $this->title = $this->href;
        }

        if (strpos($this->href, 'http') === false) {
            $this->title = $this->href;
            $this->href = url($this->page . ':attachments/' . $this->href);
        }

        // TODO:添付ファイルの処理
        // TODO:ページに貼り付けられた添付ファイルの処理
    }
}
