<?php
/**
 * 基底要素クラス.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2013-2014,2018-2019 Logue
 * @license   MIT
 */

namespace App\LukiWiki\Element;

use App\LukiWiki\AbstractElement;
use App\LukiWiki\Inline\Media;
use App\LukiWiki\Rules\HeadingAnchor;

/**
 * RootElement.
 */
class RootElement extends AbstractElement
{
    const MULTILINE_DELIMITER = "\r";

    protected $id;
    protected $count = 0;

    public function __construct(string $page, array $option)
    {
        $this->id = $option['id'] ?? 0;
        $this->page = $page;
        parent::__construct();
    }

    public function parse(array $lines)
    {
        $this->last = $this;
        $matches = [];

        $count = \count($lines);
        for ($i = 0; $i < $count; $i++) {
            $line = rtrim(array_shift($lines), "\t\r\n\0\x0B");	// スペース以外の空白文字をトリム;

            // Empty
            if (empty($line)) {
                $this->last = $this;
                continue;
            }

            if (preg_match('/^(LEFT|CENTER|RIGHT|JUSTIFY|TOP|MIDDLE|BOTTOM|CLEAR):(.*)$/', $line, $matches)) {
                $cmd = strtolower($matches[1]);

                if (!empty($cmd)) {
                    if (\is_object($this->last)) {
                        $this->last = $this->last->add(new Align($cmd));
                    }
                }
                if (empty($matches[2])) {
                    continue;
                }
                $line = $matches[2];
            }

            // Multiline-enabled block plugin #plugin{{ ... }}
            if (preg_match('/^@[^{]+(\{\{+)\s*$/', $line, $matches)) {
                $len = \strlen($matches[1]);
                $line .= "\r";
                while (!empty($lines)) {
                    $next_line = preg_replace('/[\r\n]*$/', '', array_shift($lines));
                    if (preg_match('/\}{'.$len.'}/', $next_line)) {
                        $line .= $next_line;
                        break;
                    }
                    $line .= $next_line .= "\r";
                }
            }

            // 整形済みテキスト
            $lang = null;
            if (preg_match('/^```/', $line, $matches)) {
                $line .= "\r";
                while (!empty($lines)) {
                    $next_line = preg_replace('/[\r\n]*$/', '', array_shift($lines));
                    if (preg_match('/^```$/', $next_line)) {
                        $line .= $next_line;
                        break;
                    }
                    $line .= $next_line .= "\r";
                }
            }

            // The first character
            $head = $line[0];

            // Line Break
            if (substr($line, -1) === '~') {
                $line = substr($line, 0, -1)."\r";
            }

            // Other Character
            if (\is_object($this->last)) {
                $content = null;
                switch ($head) {
                    case '#':
                        $this->insert(new Heading($this, $line, $this->page));
                        continue 2;
                        break;
                    case '`':
                        // GFM:pre
                        if (preg_match('/^```(.+)\r```$/m', $line, $matches)) {
                            $content = new PreformattedText($this, $matches[1]);
                        }
                        break;
                    case '-':
                        // List / Holizonal
                        if (substr($line, -1) === '-') {
                            // Horizontal Rule
                            $this->insert(new HorizontalRule($this, $line, $this->page));
                            continue 2;
                        }
                        // no break
                    case '+':
                    case '*':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case ' ':
                        if (preg_match('/^\s{0,3}(\-|\+|\*|\d+\.)\s+.*$/', $line, $matches)) {
                            if ($matches[1] === '-' || $matches[1] === '*') {
                                $content = new UnorderedList($this, $line, $this->page);
                            } else {
                                $content = new OrderedList($this, $line, $this->page);
                            }
                        }
                        break;
                    case '>':
                    case '<':
                        $content = new Blockquote($this, $line, $this->page);
                        break;
                    case ':':
                        $content = new DefinitionList($line, $this->page);

                        break;
                    case '|':
                        $content = new Table($line, $this->page);
                        break;
                    case '@':
                        $matches = [];

                        if (preg_match('/^@([^\(\{]+)(?:\(([^\r]*)\))?(\{*)/', $line, $matches)) {
                            // @プラグイン名(パラメータ1[,パラメータ2...]){ボディ}
                            // プラグイン名
                            $plugin = $matches[1];
                            // パラメータ
                            $params = trim($matches[2]);
                            // 階層
                            $level = \strlen($matches[3]);

                            $m2 = [];
                            $body = '';
                            if (preg_match('/\{{'.$level.'}\s*\r(.*)\r\}{'.$level.'}/', $line, $m2)) {
                                $body = trim(str_replace("\r", "\n", $m2[1]));
                            }

                            // ※エスケープ処理はプラグイン内で行うこと

                            $content = new BlockPlugin([$plugin, $params, $body], $this->page);
                        }
                        break;
                    case '~':
                        $content = new Paragraph($line, $this->page);
                        break;
                    case '/':
                        // Escape comments
                        if ($line[1] === '/') {
                            continue 2;
                        }
                        break;
                    case'!':
                        // Block Media
                        //$media = new Media()
                    default:
                        $content = new InlineElement($line, $this->page);
                        break;
                }
            }

            // Default
            if (\is_object($content)) {
                $meta = $content->getMeta();

                if (!empty($meta)) {
                    foreach ($meta as $key => $value) {
                        $this->meta[$key][] = $value;
                    }
                }
                $this->last = $this->last->add($content);
            }
            unset($content);
        }
    }

    public function getAnchor($text, $level)
    {
        // Heading id (auto-generated)
        $autoid = 'content_'.$this->id.'_'.$this->count;
        $this->count++;

        list($_text, $id, $level) = HeadingAnchor::get($text, false); // Cut fixed-anchor from $text

        $this->meta['contents'][] = str_repeat(' ', $level).'- ['.$_text.'](#'.$autoid.')';

        // Add heding
        return [$_text, null, $autoid];
    }

    public function canContain(object $obj)
    {
        return true;
    }

    public function insert($obj)
    {
        if ($obj instanceof InlineElement) {
            $obj = $obj->toPara();
        }

        return parent::insert($obj);
    }
}
