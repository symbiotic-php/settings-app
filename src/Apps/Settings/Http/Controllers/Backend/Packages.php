<?php

namespace Symbiotic\Apps\Settings\Http\Controllers\Backend;

use Psr\Http\Message\ServerRequestInterface;
use Symbiotic\Apps\Settings\Http\Form\FormPackageSettingsController;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Packages\PackagesRepositoryInterface;
use Symbiotic\Routing\RouteInterface;

use function _S\view;


class Packages
{


    public function __construct(
        protected PackagesRepositoryInterface $packagesRepository,
        protected RouteInterface $route,
        protected CoreInterface $core
    ) {
    }

    public function index()
    {
        return view($this->core, 'settings::backend/packages/index', ['packages' => $this->packagesRepository]);
    }

    public function edit()
    {
        $package = $this->packagesRepository->getPackageConfig($this->route->getParam('package_id'));
        if (!$package) {
            throw new \Exception('Package (' . $this->route->getParam('package_id') . ') not found!');
        }

        // todo: method call with reflection
        if (isset($package['settings_controller'])) {
            return $this->core->make(
                rtrim($package['settings_controller'], '\\'),
                ['package' => $package]
            )->edit();
        } else {
            return $this->core->make(
                FormPackageSettingsController::class,
                ['package' => $package]
            )->edit();
        }
    }

    public function save(ServerRequestInterface $request)
    {
        $package = $this->packagesRepository->getPackageConfig($this->route->getParam('package_id'));
        if ($package) {
            if (isset($package['settings_controller'])) {
                return $this->core->make(
                    rtrim($package['settings_controller'], '\\'),
                    ['package' => $package]
                )->save($request);
            } else {
                return $this->core->make(
                    FormPackageSettingsController::class,
                    ['package' => $package]
                )->save($request);
            }
        }
    }
}