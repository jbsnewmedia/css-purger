<?php

namespace JBSNewMedia\CssPurger\Vendors;

use JBSNewMedia\CssPurger\CssPurger;

class FontAwesome extends CssPurger
{

    public function prepareContent():self
    {
        $this->addSelectors([
            ':root',
            '.fa',
            '.fas',
            '.far',
            '.fab',
            '.fa-solid',
            '.fa-regular',
            '.fa-brands',
            '.fa-classic',
            '.fa-sharp',
            '.fa-sharp-duotone',
        ]);

        return $this;
    }

}
