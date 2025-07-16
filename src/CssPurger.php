<?php

namespace JBSNewMedia\CssPurger;

class CssPurger
{
    protected string $file='';

    protected string $content='';

    protected string $cssBlockPrefix = '';

    protected string $cssBlockSuffix = '';

    protected array $cssBlocks = [];

    protected array $cssSelectors = [];

    public function __construct(string $file = '')
    {
        if ($file !== '') {
            $this->setFile($file);
            $this->loadContent();
        }

    }
    public function setFile(string $file):self
    {
        if (!file_exists($file)) {
            throw new \Exception('File not found');
        }
        $this->file = $file;

        return $this;
    }

    public function getFile():string
    {
        return $this->file;
    }

    public function setContent(string $content):self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent():string
    {
        return $this->content;
    }

    public function addSelector(string $selector):self
    {
        $this->cssSelectors[$selector] = $selector;

        return $this;
    }

    public function addSelectors(array $selectors):self
    {
        foreach ($selectors as $selector) {
            $this->cssSelectors[$selector] = $selector;
        }

        return $this;
    }

    public function removeSelector(string $selector):self
    {
        unset($this->cssSelectors[$selector]);

        return $this;
    }

    public function removeSelectors(array $selectors):self
    {
        foreach ($selectors as $selector) {
            unset($this->cssSelectors[$selector]);
        }

        return $this;
    }

    public function loadContent(): self
    {
        if ($this->file === '') {
            throw new \Exception('No file set');
        }

        $this->content = file_get_contents($this->file);

        return $this;
    }

    public function prepareContent():self
    {
        return $this;
    }

    public function runContent(): self
    {
        $matches = explode("\n}\n", $this->content);
        unset($matches[0]);

        $this->cssBlocks = [];
        foreach ($matches as $k => $v) {
            $matches[$k] = trim($v . "\n}\n");
            $key = $this->cleanSelector(substr($matches[$k], 0, strpos($matches[$k], '{')));

            if (substr($key, 0, 1) == '@') {
                $levelDown = $this->processNestedBlocks($matches[$k]);
                $this->cssBlocks[$k] = [
                    'selector' => $this->extractSelectors($key),
                    'level' => $levelDown,
                ];
            } else {
                $properties = $this->extractProperties($matches[$k]);
                $this->cssBlocks[$k] = [
                    'selector' => $this->extractSelectors($key),
                    'properties' => $properties,
                ];
            }
        }

        return $this;
    }

    protected function extractSelectors(string $key): array
    {
        $selectors=[];
        foreach (explode(',', $key) as $kk => $vv) {
            $selectors[] = trim($this->cleanSelector($vv));
        }
        return $selectors;
    }

    protected function cleanSelector(string $selector): string
    {
        return str_replace(["\n", "\r", "\t", "\e"], '', $selector);
    }

    protected function extractProperties(string $block): array
    {
        $properties = explode(';', substr($block, strpos($block, '{') + 1));
        return array_filter(array_map('trim', $properties), fn($prop) => !empty($prop) && $prop !== '}');
    }

    protected function processNestedBlocks(string $block): array
    {
        $levelDown = explode("}\n", substr($block, strpos($block, '{') + 1));
        unset($levelDown[count($levelDown) - 1]);

        foreach ($levelDown as $kk => $vv) {
            $levelDown[$kk] = trim($vv . "\n}\n");
            $keyDown = $this->cleanSelector(substr($levelDown[$kk], 0, strpos($levelDown[$kk], '{')));
            $properties = $this->extractProperties($levelDown[$kk]);
            $levelDown[$kk] = [
                'selector' => $this->extractSelectors($keyDown),
                'properties' => $properties,
            ];
        }

        return $levelDown;
    }

    public function generateOutput(bool $min = true): string
    {
        $this->checkSelectors();

        $output = '';
        $output .= $this->cssBlockPrefix;
        foreach ($this->cssBlocks as $v) {
            if (isset($v['level'])) {
                if ($min) {
                    $output .= implode(',', $v['selector']) . "{";
                } else {
                    $output .= implode(', ', $v['selector']) . " {\n";
                }
                foreach ($v['level'] as $vv) {
                    if ($min) {
                        $output .= implode(",", $vv['selector']) . "{";
                    } else {
                        $output .= '    '.implode(", ", $vv['selector']) . "{\n";
                    }
                    foreach ($vv['properties'] as $prop) {
                        if ($min) {
                            $output .= $prop . ";";
                        } else {
                            $output .= '        '.$prop . ";\n";
                        }
                    }
                    if ($min) {
                        $output .= "}";
                    } else {
                        $output .= "    }\n";
                    }
                }
                if ($min) {
                    $output .= "}";
                } else {
                    $output .= "}\n\n";
                }
            } else {
                if ($min) {
                    $output .= implode(',', $v['selector']) . "{";
                } else {
                    $output .= implode(', ', $v['selector']) . " {\n";
                }
                foreach ($v['properties'] as $prop) {
                    if ($min) {
                        $output .= $prop . ";";
                    } else {
                        $output .= '    '.$prop . ";\n";
                    }
                }
                if ($min) {
                    $output .= "}";
                } else {
                    $output .= "}\n\n";
                }
            }
        }
        $output .= $this->cssBlockSuffix;

        return $output;
    }

    protected function checkSelectors():self
    {
        foreach ($this->cssBlocks as $key => $entry) {
            if (isset($entry['level'])) {
                foreach ($entry['level'] as $keyLevel => $level) {
                    foreach ($level['selector'] as $keySelector => $selector) {
                        if ($this->checkSelectorToRemove($selector) !== true) {
                            unset($this->cssBlocks[$key]['level'][$keyLevel]['selector'][$keySelector]);
                        }
                        if ($this->cssBlocks[$key]['level'][$keyLevel]['selector']===[]) {
                            unset($this->cssBlocks[$key]['level'][$keyLevel]);
                        }
                    }
                    if ($this->cssBlocks[$key]['level']===[]) {
                        unset($this->cssBlocks[$key]);
                    }
                }
            } else {
                foreach ($entry['selector'] as $keySelector => $selector) {
                    if ($this->checkSelectorToRemove($selector) !== true) {
                        unset($this->cssBlocks[$key]['selector'][$keySelector]);
                    }
                    if ($this->cssBlocks[$key]['selector']===[]) {
                        unset($this->cssBlocks[$key]);
                    }
                }
            }
        }

        return $this;
    }

    protected function checkSelectorToRemove(string $selector):bool
    {
        if ((strpos($selector, ':') !== false) && (strpos($selector, ':') > 0)) {
            $selector = trim(substr($selector, 0, strpos($selector, ':')));
        }

        if (strpos($selector, '>')) {
            $selector = trim(substr($selector, 0, strpos($selector, '>')));
        }

        foreach ($this->cssSelectors as $selectorCheck) {
            if ($selector == $selectorCheck) {

                return true;
            }
        }

        return false;
    }

}