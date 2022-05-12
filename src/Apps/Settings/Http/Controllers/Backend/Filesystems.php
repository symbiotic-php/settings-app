<?php

namespace Symbiotic\Apps\Settings\Http\Controllers\Backend;

use Psr\Http\Message\ServerRequestInterface;
use Symbiotic\Apps\Settings\Events\FilesystemAdapterForms;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\View\View;
use Symbiotic\Form\Form;
use Symbiotic\Form\FormInterface;
use Symbiotic\Settings\SettingsRepositoryInterface;
use function _S\event;
use function _S\redirect;
use function _S\route;
use function _S\settings;

class Filesystems
{
    protected CoreInterface $core;

    protected SettingsRepositoryInterface $settings_repository;

    protected array $errors = [
        'fields' => []
    ];

    /**
     * @var FilesystemAdapterForms
     */
    protected $filesystems_forms = null;

    public function __construct(CoreInterface $core, SettingsRepositoryInterface $repository)
    {
        $this->core = $core;
        $this->settings_repository = $repository;
        $forms = new FilesystemAdapterForms();
        $form = new Form([
            'fields' =>
                [
                    [
                        'type' => 'text',
                        'name' => 'name',
                        'label' => 'Название',
                        'default' => $core('public_path', $core->getBasePath()),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'path',
                        'label' => 'Директория',
                        'default' => $core('public_path', $core->getBasePath())
                    ],
                    [
                        'type' => 'bool',
                        'name' => 'visibility',
                        'label' => 'Публичное',
                        'default' => 1
                    ],
                    [
                        'type' => 'submit',
                        'name' => '',
                        'default' => 'Send',
                    ]
                ]

        ]);
        $forms->add('local', $form);
        $this->filesystems_forms = event($forms);

    }

    public function index(): View
    {
        return \_S\view('backend/filesystems/index', [
            'filesystems' => settings('filesystems'),
        ]);
    }

    public function edit(CoreInterface $core, ServerRequestInterface $request)
    {


        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $path = rtrim($data['path'], '\\/');
            $id = !empty($data['id']) ? $data['id'] : md5($path);
            $form = $this->filesystems_forms->getForm($data['type']);
            $validator = $form->getValidator($data);
            if ($validator->validate()) {
                unset($data['id']);
                $this->saveFilesystem($id, $data);
            }
        } else {
            $id = $request->getAttribute('id');
            $config = $this->getFilesystemConfig($id);
            if (null === $config) {
                return \_S\view('backend/error', ['error' => 'Filesystem  (' . $id . ') not found!']);
            }
            $form = $this->filesystems_forms->getForm($config['driver']);

            $config['id'] = $id;
            $form->setValues($config);

            return \_S\view('backend/filesystems/edit', [
                'form' => $form
            ]);

        }

        //$this->saveFilesystem($data);

        if (!isset($data['id'])) {
            return $this->add($core, $request);
        } else {
            return $this->edit($core, $request);
        }
    }


    public function add(ServerRequestInterface $request)
    {

        $driver = $request->getAttribute('driver');
        if (!$driver) {
            return redirect(route('backend:settings::filesystems.selectType'));
        }

        $form = $this->getDriverForm($driver);

        $form->setAction(route('backend:settings::filesystems.add', ['driver' => $driver]));

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $id = !empty($data['id']) ? $data['id'] : $this->filesystems_forms->generateId($driver, $data);

            $validator = $form->getValidator($data);
            if ($validator->validate()) {
                unset($data['id']);
                if ($this->saveFilesystem($id, $data)) {
                    return redirect(route('backend:settings::filesystems.edit', ['id' => $id]));
                }
            }
        }

        return \_S\view('backend/filesystems/add', [
            'form' => $form
        ]);
    }

    public function selectType(ServerRequestInterface $request)
    {

        $form = new Form(['fields' => [

            [
                'type' => 'select',
                'name' => 'type',
                'title' => 'Select Adapter',
                'default' => 'local',
                'variants' => $this->filesystems_forms->getFilesystemTypes()
            ],
            [
                'type' => 'submit',
                'name' => '',
                'default' => 'Select',
            ]

        ]]);
        if ($request->getMethod() === 'POST') {
            $post = $request->getParsedBody();
            $validator = $form->getValidator($post);
            if ($validator->validate()) {
                return redirect(route('backend:settings::filesystems.add', $post));
            } else {
                $errors = $validator->getErrors();
            }
        }


        return \_S\view('backend/filesystems/selectType', [
            'form' => $form
        ]);

    }


    /**
     * @param string $id
     * @param array $data
     */
    protected function saveFilesystem(string $id, array $data)
    {
        $filesystems = settings('filesystems');
        $filesystems->set($id, $data);
        /**
         * @throws
         */
        return $this->settings_repository->save('filesystems', $filesystems);
    }

    /**
     * @param string $id
     * @return array|null
     */
    protected function getFilesystemConfig(string $id): ?array
    {
        return settings('filesystems')->get($id);
    }

    protected function getDriverForm(string $driver): ?FormInterface
    {
        $form = $this->filesystems_forms->getForm($driver);
        if (!$form) {
            return null;
        }
        $form->addField('hidden', ['name' => 'id']);
        $form->addField('hidden', ['name' => 'driver', 'value' => $driver]);

        return $form;
    }


}