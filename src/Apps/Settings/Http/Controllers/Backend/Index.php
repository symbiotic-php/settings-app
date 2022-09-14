<?php

namespace Symbiotic\Apps\Settings\Http\Controllers\Backend;

use Symbiotic\Apps\AppsRepositoryInterface;
use Symbiotic\View\View;
use Symbiotic\View\ViewFactory;

class Index
{
    public function __construct(protected ViewFactory $view)
    {
    }

    public function index(AppsRepositoryInterface $appsRepository)
    {
       // return '';
        return $this->view->make('settings::backend/index', ['apps' => $appsRepository]);
    }
    public function indfdex()
    {
       // return '';
        return $this->view->make('backend/index');
    }
}