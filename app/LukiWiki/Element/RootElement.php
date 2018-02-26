<?php
/**
 * 基底要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\Rules\HeadingAnchor;

/**
 * RootElement.
 */
class RootElement extends Element
{
    const MULTILINE_DELIMITER = "\r";

    protected $id;
    protected $count = 0;
    protected $contents;
    protected $contents_last;
    protected $comments = [];

    public function __construct($id)
    {
        $this->id = $id;
        $this->contents = new parent();
        $this->contents_last = $this->contents;
        parent::__construct();
    }

    public function parse($lines)
    {
        $this->last = &$this;
        $matches = [];

        while (!empty($lines)) {
            $line = array_shift($lines);

            // Escape comments
            if (substr($line, 0, 2) === '//') {
                if ($this->is_guiedit) {
                    $this->comments[] = substr($line, 2);
                    $line = '___COMMENT___';
                } else {
                    continue;
                }
            }

            // Extend TITLE by miko
            if (preg_match('/^(TITLE):(.*)$/', $line, $matches)) {
                /*
                static $newbase;
                if (!isset($newbase)) {
                    $newbase = trim(strip_tags(RendererFactory::factory($matches[2])));
                    // For BugTrack/132.
                    $newtitle = htmlspecialchars($newbase, ENT_HTML5, 'UTF-8');
                }
                */
                continue;
            }

            if (preg_match('/^(LEFT|CENTER|RIGHT|JUSTIFY):(.*)$/', $line, $matches)) {
                // <div style="text-align:...">
                $align = new Align(strtolower($matches[1]));
                $this->last = $this->last->add($align);
                if (empty($matches[2])) {
                    continue;
                }
                $line = $matches[2];
            }

            $line = rtrim($line, "\t\r\n\0\x0B");	// スペース以外の空白文字をトリム

            // Empty
            if (empty($line)) {
                $this->last = &$this;
                continue;
            }

            // Horizontal Rule
            if (substr($line, 0, 4) == '----') {
                $hrule = new HRule($this, $line);
                $this->insert($hrule);
                continue;
            }

            // Multiline-enabled block plugin #plugin{{ ... }}
            if (preg_match('/^#[^{]+(\{\{+)\s*$/', $line, $matches)) {
                $len = strlen($matches[1]);
                $line .= self::MULTILINE_DELIMITER;
                while (!empty($lines)) {
                    $next_line = preg_replace('/['.self::MULTILINE_DELIMITER.'\n]*$/', '', array_shift($lines));
                    if (preg_match('/\}{'.$len.'}/', $next_line)) {
                        $line .= $next_line;
                        break;
                    } else {
                        $line .= $next_line .= self::MULTILINE_DELIMITER;
                    }
                }
            }

            // The first character
            $head = $line[0];

            // Heading
            if ($head === '*') {
                $heading = new Heading($this, $line);
                $this->insert($heading);
                continue;
            }

            // Pre
            if ($head === ' ' || $head === "\t") {
                $pre = new Pre($this, $line);
                $this->last = $this->last->add($pre);
                continue;
            }

            // CPre (Plus!)
            if (substr($line, 0, 2) === '# ' or substr($line, 0, 2) == "#\t") {
                $sharppre = new SharpPre($this, $line);
                $this->last = $this->last->add($sharppr);
                continue;
            }

            // Line Break
            if (substr($line, -1) === '~') {
                $line = substr($line, 0, -1)."\r";
            }

            // Other Character
            if (gettype($this->last) === 'object') {
                switch ($head) {
                    case '-':
                        $content = new UList($this, $line);
                        break;
                    case '+':
                        $content = new OList($this, $line);
                        break;
                    case '>':
                    case '<':
                        $content = new Blockquote($this, $line);
                        break;
                    // ここからはファクトリークラスを通す
                    case ':':
                        $content = ElementFactory::factory('DList', $this, $line);
                        break;
                    case '|':
                        $content = ElementFactory::factory('Table', $this, $line);
                        break;
                    case ',':
                        $content = ElementFactory::factory('YTable', $this, $line);
                        break;
                    case '#':
                        $content = ElementFactory::factory('Plugin', $this, $line);
                        break;
                    default:
                        $content = ElementFactory::factory('InlineElement', null, $line);
                        break;
                }

                // Default
                $this->last = $this->last->add($content);
                unset($content);
                continue;
            }
        }
    }

    public function getAnchor($text, $level)
    {
        // Heading id (auto-generated)
        $autoid = 'content_'.$this->id.'_'.$this->count;
        ++$this->count;

        list($_text, $id, $level) = HeadingAnchor::get($text, false); // Cut fixed-anchor from $text

        $anchor = ' &edit(,'.$id.');';

        // Add 'page contents' link to its heading
        $contents = new ContentsList($_text, $level, $id);
        $this->contents_last = $this->contents_last->add($contents);

        // Add heding
        return [$_text.$anchor, $this->count > 1 ? "\n" : '', $autoid];
    }

    public function insert(&$obj)
    {
        if ($obj instanceof InlineElement) {
            $obj = $obj->toPara();
        }

        return parent::insert($obj);
    }

    public function toString()
    {
        // #contents
        return preg_replace_callback('/<#_contents_>/', [$this, 'replaceContents'], parent::toString());
    }

    private function comment($matches)
    {
        $comments = explode("\n", $matches[0]);
        foreach ($comments as &$comment) {
            $comment = array_shift($this->comments);
        }
        $comment = implode("\n", $comments);

        return '<span class="fa fa-comment" title="'.strip_tags($comment).'"></span>';
    }

    private function replaceContents()
    {
        //var_dump($this->contents->toString());
        return '<div class="contents" id="contents_'.$this->id.'">'."\n".
            $this->contents->toString().
            '</div>'."\n";
    }
}
