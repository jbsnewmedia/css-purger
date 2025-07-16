<?php

namespace JbsNewMedia\CssPurger;

class CssPurgerBootstrap extends CssPurger
{

    public function prepareContent():self
    {
        $this->cssBlockPrefix = substr($this->content, 0, strpos($this->content, ':root'));
        $this->content = str_replace("*/\n:root,", "*/\n}\n:root,", $this->content);

        return $this;
    }

}