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
        if (strpos($this->content, '@charset') !== false) {
            $this->cssBlockPrefix = substr($this->content, 0, strpos($this->content, ';') + 1);
            $this->content = substr($this->content, strpos($this->content, ';') + 1);
        }

        if (strpos(trim($this->content), '/*') === 0) {
            $this->cssBlockPrefix .= substr($this->content, 0, strpos($this->content, '*/') + 2);
            $this->content = substr($this->content, strpos($this->content, '*/') + 2);
        }

        $matches = explode("\n}\n", trim($this->content));
        $this->cssBlocks = [];
        foreach ($matches as $k => $v) {
            $v = trim($v);
            if (empty($v)) continue;
            
            $v .= "\n}\n";
            $key = $this->cleanSelector(substr($v, 0, strpos($v, '{')));

            if (substr($key, 0, 1) == '@' && strpos($key, '@font-face') === false) {
                $levelDown = $this->processNestedBlocks($v);
                $this->cssBlocks[$k] = [
                    'selector' => $this->extractSelectors($key),
                    'level' => $levelDown,
                ];
            } else {
                $properties = $this->extractProperties($v);
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
        $propertiesContent = substr($block, strpos($block, '{') + 1);
        $propertiesContent = trim($propertiesContent);
        if (substr($propertiesContent, -1) === '}') {
            $propertiesContent = substr($propertiesContent, 0, -1);
        }
        $propertiesContent = trim($propertiesContent);
        
        $properties = explode(';', $propertiesContent);
        $properties = array_filter(array_map('trim', $properties), fn($prop) => !empty($prop));
        
        return $properties;
    }

    protected function processNestedBlocks(string $block): array
    {
        $content = trim(substr($block, strpos($block, '{') + 1));
        $content = substr($content, 0, -1); // Remove last }
        
        $levelDown = explode("}\n", $content);
        $result = [];

        foreach ($levelDown as $vv) {
            $vv = trim($vv);
            if (empty($vv)) continue;
            
            $vv .= "\n}\n";
            $keyDown = $this->cleanSelector(substr($vv, 0, strpos($vv, '{')));
            $properties = $this->extractProperties($vv);
            $result[] = [
                'selector' => $this->extractSelectors($keyDown),
                'properties' => $properties,
            ];
        }

        return $result;
    }

    public function generateOutput(bool $min = true): string
    {
        $this->checkSelectors();

        $output = '';

        $header = '/* Purged by CssPurger (https://jbs-newmedia.de/css-purger) - MIT License - JBS New Media GmbH, Juergen Schwind */'."\n";

        if (strpos($this->cssBlockPrefix, '@charset') !== false) {
            $output .= preg_replace('/(@charset [^;]+;)/', '$1'."\n".$header, $this->cssBlockPrefix);
        } else {
            $output .= $header.$this->cssBlockPrefix;
        }

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
        $selector = trim($selector);
        if ($selector === '') {
            return false;
        }

        // Handle multiple selectors joined by space, >, +, ~
        // We split by these combinators and check if each part is "valid"
        $parts = preg_split('/[\s>+~]+/', $selector, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            if (!$this->checkSingleSelector($part)) {
                return false;
            }
        }

        return true;
    }

    protected function checkSingleSelector(string $selector): bool
    {
        // Remove pseudo-classes/elements
        if (($pos = strpos($selector, ':')) !== false && $pos > 0) {
            $selector = substr($selector, 0, $pos);
        }

        // Handle multiple classes on same element: .class1.class2
        // We split by . and check each class
        if (str_contains($selector, '.') && strlen($selector) > 1 && $selector[0] === '.') {
            $classes = explode('.', ltrim($selector, '.'));
            foreach ($classes as $class) {
                if (!$this->isSelectorInList('.' . $class)) {
                    return false;
                }
            }
            return true;
        }

        return $this->isSelectorInList($selector);
    }

    protected function isSelectorInList(string $selector): bool
    {
        foreach ($this->cssSelectors as $selectorCheck) {
            if ($selector === $selectorCheck) {
                return true;
            }
        }
        return false;
    }

}
