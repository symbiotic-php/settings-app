<?php

namespace Symbiotic\Apps\Settings;

use Symbiotic\View\View;
use Symbiotic\Packages\PackagesRepositoryInterface;


class ViewHelper
{

    public function packagesList(PackagesRepositoryInterface $Repository)
    {
        return $this->view->make('backend/PackagesList', ['packages' => $Repository]);
    }
}