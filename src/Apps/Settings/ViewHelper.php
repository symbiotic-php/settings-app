<?php

namespace Symbiotic\Apps\Settings;

use Symbiotic\Core\View\View;
use Symbiotic\Packages\PackagesRepositoryInterface;


class ViewHelper
{

    public function packagesList(PackagesRepositoryInterface $Repository)
    {
        return View::make('backend/PackagesList', ['packages' => $Repository]);
    }
}