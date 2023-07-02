<?php

namespace Symbiotic\Apps\Settings\Http\Controllers\Backend;


use Psr\Http\Message\ServerRequestInterface;
use Symbiotic\Apps\Settings\Events\FilesystemAdapterForms;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\Events\CacheClear;
use Symbiotic\Form\FormBuilder;
use Symbiotic\View\View;
use Symbiotic\Form\FormInterface;
use Symbiotic\Settings\SettingsRepositoryInterface;

use Symbiotic\View\ViewFactory;

use function _S\event;
use function _S\redirect;
use function _S\route;
use function _S\settings;

class Filesystems
{
    protected array $errors = [
        'fields' => []
    ];

    /**
     * @var FilesystemAdapterForms|null
     */
    protected ?FilesystemAdapterForms $filesystems_forms = null;

    public function __construct(
        protected CoreInterface $core,
        protected SettingsRepositoryInterface $settingsRepository,
        protected ViewFactory $view
    ) {
        $this->filesystems_forms = $this->getFilesystemForms($core);
    }

    private function getFilesystemForms(CoreInterface $core): FilesystemAdapterForms
    {
        $forms = new FilesystemAdapterForms();
        $form = $core->get(FormBuilder::class)->createFromArray(
            [
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
                            'type' => 'text',
                            'name' => 'url',
                            'label' => 'Публичный адрес',
                            'default' => 'https://'.$core('config::default_host').$core('base_uri', ''),
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
                        ],
                    ]

            ]
        );

        $forms->add('local', $form);

        return event($core, $forms);
    }

    public function index(CoreInterface $core): View
    {
        return $this->view->make('backend/filesystems/index', [
            'filesystems' => settings($core, 'filesystems'),
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
                return $this->view->make('backend/error', ['error' => 'Filesystem  (' . $id . ') not found!']);
            }
            $form = $this->filesystems_forms->getForm($config['driver']);

            $config['id'] = $id;
            $form->setValues($config);

            return $this->view->make('backend/filesystems/edit', [
                'form' => $form
            ]);
        }

        //$this->saveFilesystem($data);

        if (!isset($data['id'])) {
            return $this->add($request);
        } else {
            return $this->edit($request);
        }
    }


    public function add(ServerRequestInterface $request)
    {
        $driver = $request->getAttribute('driver');
        if (!$driver) {
            return redirect($this->core, route($this->core, 'backend:settings::filesystems.selectType'), 302);
        }

        $form = $this->getDriverForm($driver);

        $form->setAction(route($this->core, 'backend:settings::filesystems.add', ['driver' => $driver]));

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $id = !empty($data['id']) ? $data['id'] : $this->filesystems_forms->generateId($driver, $data);

            $validator = $form->getValidator($data);
            if ($validator->validate()) {
                unset($data['id']);
                if ($this->saveFilesystem($id, $data)) {
                    event($this->core, new CacheClear('all'));
                    return redirect(
                        $this->core,
                        route($this->core, 'backend:settings::filesystems.edit', ['id' => $id]),
                        302
                    );
                }
            }
        }

        return $this->view->make('backend/filesystems/add', [
            'form' => $form
        ]);
    }

    public function selectType(ServerRequestInterface $request, FormBuilder $formBuilder)
    {
        $form = $formBuilder->createFromArray(
            [
                'fields' => [
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

                ]
            ]
        );
        if ($request->getMethod() === 'POST') {
            $post = $request->getParsedBody();
            $validator = $form->getValidator($post);
            if ($validator->validate()) {
                return redirect($this->core, route($this->core, 'backend:settings::filesystems.add', $post), 302);
            } else {
                $errors = $validator->getErrors();
            }
        }


        return $this->view->make('backend/filesystems/selectType', [
            'form' => $form
        ]);
    }


    /**
     * @param string $id
     * @param array  $data
     */
    protected function saveFilesystem(string $id, array $data)
    {
        $filesystems = settings($this->core, 'filesystems');
        $filesystems->set($id, $data);
        /**
         * @throws
         */
        return $this->settingsRepository->save('filesystems', $filesystems);
    }

    /**
     * @param string $id
     *
     * @return array|null
     */
    protected function getFilesystemConfig(string $id): ?array
    {
        return settings($this->core, 'filesystems')->get($id);
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