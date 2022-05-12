<?php

namespace Symbiotic\Apps\Settings\Http\Form;

use Psr\Http\Message\ServerRequestInterface;
use Symbiotic\Core\View\View;
use Symbiotic\Form\Form;
use Symbiotic\Settings\PackageSettingsControllerAbstract;
use Symbiotic\Settings\Settings;
use Symbiotic\Form\FormInterface;
use function _S\collect;
use function _S\route;

class FormPackageSettingsController extends PackageSettingsControllerAbstract
{


    /**
     * @param ServerRequestInterface $request
     * @return View
     * @throws \Exception
     */
    public function save(ServerRequestInterface $request): View
    {
        $data = $request->getParsedBody();

        $form  = $this->getForm();
        if($form) {
            $form->setValues($data);
        }
        if (!$form->getValidator($data)->validate()) {
            return $this->edit(false, $this->errors);
        }
        $settings = collect();
        /**
         * set deep dot items 'filesystems.local.path' = ['filesystems' => ['local' => ['path' => $v]]]
         */
        foreach ($data as $k => $v) {
            $settings->set($k, $v);
        }
        /**
         * @throws
         */
        $this->settings_repository->save($this->package->getId(), new Settings($settings->all()));

        return $this->edit(true);
    }



    public function edit($saved = null, $errors = null): View
    {
        $form   = $this->getForm();
        if($form) {
            $form ->setAction(route('backend:settings::package.save', ['package_id' => $this->package->getId()]));
        }
        $form->addField('submit', ['default' => 'Send']);
        $form->setValues($this->getPackageSettings()->all());
        return View::make('settings::backend/packages/settings_form', [
            'package' => $this->package,
            'form' => $form,
           /// 'settings' => collect($this->getPackageSettings()->all()),
            'saved' => $saved,
            'errors' => $errors
        ]);
    }

    /**
     * @return FormInterface|null
     * @throws \Error
     */
    protected function getForm(): ? FormInterface
    {
        $package = $this->package;

        if ($package->has('settings_form')) {
            $class = $package->get('settings_form');
            return new $class;// throw not exists

        } elseif ($package->has('settings_fields')) {
            return new Form(['fields'=>$package->get('settings_fields')]);// throw is not array
        }
        return null;
    }
}