<?php

declare(strict_types=1);

namespace Symbiotic\Apps\Settings\Http\Controllers\Backend;

use Psr\Http\Message\ServerRequestInterface;
use Symbiotic\Apps\Settings\Form\SystemSettingsForm;
use Symbiotic\Apps\Settings\Form\FieldFilesystemSelect;
use Symbiotic\Core\Config;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\Events\CacheClear;
use Symbiotic\Form\FormBuilder;
use Symbiotic\Form\FormInterface;
use Symbiotic\Routing\UrlGeneratorInterface;
use Symbiotic\View\View;
use Symbiotic\Settings\Settings;
use Symbiotic\Settings\SettingsRepositoryInterface;

use Symbiotic\View\ViewFactory;

use function _S\collect;
use function _S\event;
use function _S\route;
use function _S\settings;

class System
{

    protected ?FormInterface $form;

    public function __construct(
        protected CoreInterface $core,
        protected SettingsRepositoryInterface $settingsRepository,
        protected ViewFactory $view
    ) {
        $this->form = $this->initForm();
    }

    protected function initForm(): SystemSettingsForm
    {
        /**
         * @var FormBuilder            $formBuilder
         * @var Config                 $config
         * @var ServerRequestInterface $request
         */
        $config = $this->core['config'];
        $request = $this->core['request'];
        $formBuilder = $this->core->get(FormBuilder::class);
        $uri = $request->getUri();
        $port = $uri->getPort();
        if (in_array($port, [80, 443])) {
            $port = '';
        }
        $form = [
            'fields' => [
                [
                    'type' => 'text',
                    'name' => 'default_host',
                    'label' => 'Default host',
                    'default' => $uri->getHost() . (!empty($port) ? $port : ''),// port?
                ],
                [
                    'type' => 'text',
                    'name' => 'uri_prefix',
                    'label' => 'Глобальный префикс Uri',
                    'default' => $config['uri_prefix']
                ],
                [
                    'type' => 'text',
                    'name' => 'backend_prefix',
                    'label' => 'Admin префикс Uri',
                    'default' => $config['backend_prefix'],
                ],
                [
                    'type' => 'text',
                    'name' => 'assets_prefix',
                    'label' => 'Префикс Uri для статичных файлов',
                    'default' => $config['assets_prefix'],
                ],
                [
                    'label' => 'Assets filesystem',
                    'type' => FieldFilesystemSelect::TYPE,
                    'name' => 'assets_filesystem',
                ],
                [
                    'label' => 'Media filesystem',
                    'type' => FieldFilesystemSelect::TYPE,
                    'name' => 'media_filesystem',
                ],
                [
                    'type' => 'bool',
                    'name' => 'debug',
                    'label' => 'Debug',
                    'default' => (int)$config['debug']
                ],
                [
                    'type' => 'bool',
                    'name' => 'symbiosis',
                    'label' => 'Режим симбиоза',
                    'default' => (int)$config['symbiosis']

                ],
                [
                    'type' => 'bool',
                    'name' => 'packages_settlements',
                    'label' => 'Поселения приложений по их ID',
                    'default' => (int)$config['packages_settlements']
                ],
                [
                    'type' => 'submit',
                    'default' => 'Save',
                ]
            ]
        ];

        return \_S\event($this->core, $formBuilder->createFromArray($form, SystemSettingsForm::class));
    }

    public function coreSave(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
        $errors = [
            'fields' => []
        ];

        /**
         * todo: refactor in forms objects
         * ЭТО же жесть)))
         */
        if (!\filter_var($data['default_host'], \FILTER_VALIDATE_DOMAIN)) {
            $errors['fields']['default_host'] = 'Не валидный домен!';
        }
        $bp = $data['backend_prefix'] = trim($data['backend_prefix'], '\\/');
        if (empty($bp)) {
            $errors['fields']['backend_prefix'] = 'Admin префикс не может быть пустым!';
        } elseif (\filter_var('s.ru/' . $bp, \FILTER_VALIDATE_URL)) {
            $errors['fields']['backend_prefix'] = 'Admin не корректный!';
        }
        $ap = $data['assets_prefix'] = trim($data['assets_prefix'], '\\/');
        if (empty(trim($ap, '\\/'))) {
            $errors['fields']['assets_prefix'] = 'Префикс статики не может быть пустым!';
        } elseif (\filter_var('s.ru/' . $ap, \FILTER_VALIDATE_URL)) {
            $errors['fields']['assets_prefix'] = 'Префикс не корректный!';
        }
        if (!empty($errors['fields'])) {
            return $this->coreEdit(false, $errors);
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
        $this->settingsRepository->save('core', new Settings($settings->all()));
        event($this->core, new CacheClear('all'));
        if ($settings->get('backend_prefix') !== ($this->core) ('config::backend_prefix')) {
            $redirect = $this->core->get(UrlGeneratorInterface::class)->to(
                rtrim($settings->get('backend_prefix'), '/') . '/settings/'
            );
            return \_S\redirect($this->core, $redirect, 302);
        }
        return $this->coreEdit(true);
    }

    public function coreEdit($saved = null, $errors = null): View
    {
        $form = $this->form;
        $form->setValues(settings($this->core, 'core')->all());
        $form->setAction(route($this->core, 'backend:settings::system.save'));
        //// todo: errors and validators

        return $this->view->make('backend/system', [
            'form' => $form,
            'saved' => $saved,
            'errors' => $errors
        ]);
    }

}