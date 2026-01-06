<?php

namespace JBSNewMedia\CssPurger\Vendors;

use JBSNewMedia\CssPurger\CssPurger;

class Bootstrap extends CssPurger
{

    public function prepareContent():self
    {
        $this->cssBlockPrefix = substr($this->content, 0, strpos($this->content, ':root'));
        $this->content = str_replace("*/\n:root,", "*/\n}\n:root,", $this->content);

        $this->addSelectors([
            ':root',
            '*',
            '[data-bs-theme=light]',
            '[data-bs-theme=dark]',
        ]);

        return $this;
    }

}
