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

    public function __construct(int $id = 0)
    {
        $this->id = $id;
        parent::__construct();
    }

    public function parse(array $lines)
    {
        $this->last = $this;
        $matches = [];

        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            $line = rtrim(array_shift($lines), "\t\r\n\0\x0B");	// スペース以外の空白文字をトリム;

            // Empty
            if (empty($line)) {
                $this->last = $this;
                continue;
            }

            if (preg_match('/^(LEFT|CENTER|RIGHT|JUSTIFY|TITLE):(.*)$/', $line, $matches)) {
                $cmd = strtolower($matches[1]);

                if ($cmd === 'title') {
                    $this->meta['title'] = $matches[2];
                } else {
                    $this->last = $this->last->add(new Align($cmd));
                }
                if (empty($matches[2])) {
                    continue;
                }
                $line = $matches[2];
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

            // Github Markdown互換シンタックスハイライト記法
            if (preg_match('/^```$/', $line, $matches)) {
                $line .= self::MULTILINE_DELIMITER;
                while (!empty($lines)) {
                    $next_line = preg_replace('/['.self::MULTILINE_DELIMITER.'\n]*$/', '', array_shift($lines));
                    if (preg_match('/^```$/', $next_line)) {
                        $line .= $next_line;
                        break;
                    } else {
                        $line .= $next_line .= self::MULTILINE_DELIMITER;
                    }
                }
            }

            // The first character
            $head = $line[0];

            // Line Break
            if (substr($line, -1) === '~') {
                $line = substr($line, 0, -1)."\r";
            }

            // Other Character
            if (is_object($this->last)) {
                $content = null;
                switch ($head) {
                    case '*':
                        $this->insert(new Heading($this, $line));
                        continue;
                        break;
                    case '`':
                        // GFM:pre
                        if (preg_match('/\```(\w+?)\r(.*)\r```/', $line, $body)) {
                            $line .= "\r".$body[1]."\r";
                        }
                        $content = new GfmPre($this, $line, $lang);
                        break;
                    case ' ':
                    case "\t":
                        // Pre
                        $content = new Pre($this, $line);
                        break;
                    case '-':
                        if (substr($line, 0, 4) === '----') {
                            // Horizontal Rule
                            $this->insert(new HRule($this, $line));
                            continue;
                        }
                        // List
                        $content = new UList($this, $line);
                        break;
                    case '+':
                        $content = new OList($this, $line);
                        break;
                    case '>':
                    case '<':
                        $content = new Blockquote($this, $line);
                        break;
                    case ':':
                        $out = explode('|', ltrim($line), 2);
                        if (!count($out) < 2) {
                            $content = new DList($out);
                        }
                        break;
                    case '|':
                        if (preg_match('/^\|(.+)\|([hHfFcC]?)$/', $line, $out)) {
                            $content = new Table($out);
                        }
                        break;
                    case ',':
                        $content = new YTable(explode(',', substr($line, 1)));
                        break;
                    case '#':
                        $matches = [];

                        if ($line[1] === ' ' || $line[1] === "\t") {
                            // CPre (Plus!)
                            $content = $this->last->add(new SharpPre($this, $line));
                        } elseif (preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(\{*)/', $line, $matches)) {
                            // Plugin
                            $len = strlen($matches[3]);
                            $body = [];
                            if (preg_match('/\{{'.$len.'}\s*\r(.*)\r\}{'.$len.'}/', $line, $body)) {
                                // Seems multiline-enabled block plugin
                                $matches[2] .= "\r".$body[1]."\r";
                            }
                            $content = new BlockPlugin($matches);
                        }
                        break;
                    case '~':
                        $content = new Paragraph(' '.substr($line, 1));
                        break;
                    case '/':
                        // Escape comments
                        if ($line[1] === '/') {
                            continue;
                        }
                        break;
                    default:
                        $content = new InlineElement($line);
                        break;
                }

                if (is_object($content)) {
                    $meta = $content->getMeta();
                    if (!empty($meta)) {
                        foreach ($meta as $key => $value) {
                            $this->meta[$key][] = $value;
                        }
                    }
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

        $this->meta['contents'][$id] = ['level' => $level, 'content' => $_text];

        // Add heding
        return [$_text, $this->count > 1 ? "\n" : '', $autoid];
    }

    public function insert($obj)
    {
        if ($obj instanceof InlineElement) {
            $obj = $obj->toPara();
        }

        return parent::insert($obj);
    }
}
