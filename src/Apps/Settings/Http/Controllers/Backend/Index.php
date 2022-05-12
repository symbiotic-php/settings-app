<?php

namespace Symbiotic\Apps\Settings\Http\Controllers\Backend;

use Symbiotic\Apps\AppsRepositoryInterface;
use Symbiotic\Core\View\View;

class Index
{
    public function index(AppsRepositoryInterface $appsRepository)
    {
       // return '';
        return View::make('settings::backend/index', ['apps' => $appsRepository]);
    }
    public function indfdex()
    {
       // return '';
        return View::make('backend/index');
    }
}