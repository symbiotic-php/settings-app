<?php

declare(strict_types=1);

namespace Symbiotic\Apps\Settings\Http\Controllers\Backend;

use Psr\Http\Message\ServerRequestInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\Events\CacheClear;
use Symbiotic\Form\FormBuilder;
use Symbiotic\Settings\Settings;
use Symbiotic\View\View;
use Symbiotic\Form\FormInterface;
use Symbiotic\Settings\SettingsRepositoryInterface;
use Symbiotic\View\ViewFactory;

use function _S\event;
use function _S\redirect;
use function _S\route;
use function _S\settings;

class Databases
{
    protected array $errors = [
        'fields' => []
    ];


    public function __construct(
        protected CoreInterface $core,
        protected SettingsRepositoryInterface $settingsRepository,
        protected ViewFactory $view,
        protected FormBuilder $formBuilder
    ) {}


    public function index(CoreInterface $container): View
    {
        if (!$container->get(SettingsRepositoryInterface::class)->has('databases')) {
            $this->createDatabasesSettingsFromCoreConfig($container);
        }
        return $this->view->make('backend/databases/index', [
            'databases' => settings($container, 'databases'),
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface|View
     * @throws \Symbiotic\Packages\ResourceExceptionInterface
     */
    public function edit(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
        if ($request->getMethod() === 'POST') {
            $id = !empty($data['id']) ? $data['id'] : md5($data['driver'] . serialize($data));
            $form = $this->getDriverForm($data['driver']);
            $validator = $form->getValidator($data);
            if ($validator->validate()) {
                unset($data['id']);
                $this->saveDatabase($id, $data);
            }
            return redirect(
                $this->core,
                route(
                    $this->core,
                    'backend:settings::databases.edit',
                    ['id' => $id]
                ),
                302
            );
        } else {
            $id = $request->getAttribute('id');
            $config = $this->getDatabaseConfig($id);
            if (null === $config) {
                return $this->view->make('backend/error', ['error' => 'Database  (' . $id . ') not found!']);
            }
            $form = $this->getDriverForm($config['driver']);

            $config['id'] = $id;
            $form->setValues($config);

            return $this->view->make('backend/databases/edit', [
                'form' => $form
            ]);
        }
    }


    public function add(ServerRequestInterface $request)
    {
        $driver = $request->getAttribute('driver');
        if (!$driver) {
            return redirect($this->core, route($this->core, 'backend:settings::databases.selectType'), 302);
        }

        $form = $this->getDriverForm($driver);

        $form->setAction(route($this->core, 'backend:settings::databases.add', ['driver' => $driver]));

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $id = !empty($data['id']) ? $data['id'] : md5($driver . serialize($data));

            $validator = $form->getValidator($data);
            if ($validator->validate()) {
                unset($data['id']);
                if ($this->saveDatabase($id, $data)) {
                    event($this->core, new CacheClear('all'));
                    return redirect(
                        $this->core,
                        route($this->core, 'backend:settings::databases.edit', ['id' => $id]),
                        302
                    );
                }
            }
        }

        return $this->view->make('backend/databases/add', [
            'form' => $form
        ]);
    }

    public function selectType(ServerRequestInterface $request, FormBuilder $formBuilder)
    {
        $variants = array_column($this->getDrivers(), 'name', 'type');

        $form = $formBuilder->createFromArray(
            [
                'fields' => [
                    [
                        'type' => 'select',
                        'name' => 'type',
                        'title' => 'Select database driver',
                        'default' => 'mysql',
                        'variants' => $variants
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
                return redirect($this->core, route($this->core, 'backend:settings::databases.add', $post), 302);
            } else {
                $errors = $validator->getErrors();
                // todo
            }
        }


        return $this->view->make('backend/databases/selectType', [
            'form' => $form
        ]);
    }

    private function createDatabasesSettingsFromCoreConfig(CoreInterface $container): void
    {
        $database = $container('config::database');
        if ($database && !empty($database['connections'])) {
            $container(SettingsRepositoryInterface::class)->save('databases', new Settings($database['connections']));
        }
    }


    /**
     * @param string $id
     * @param array  $data
     */
    private function saveDatabase(string $id, array $data)
    {
        $collection = settings($this->core, 'databases');
        $collection->set($id, $data);
        /**
         * @throws
         */
        return $this->settingsRepository->save('databases', $collection);
    }

    /**
     * @param string $id
     *
     * @return array|null
     */
    private function getDatabaseConfig(string $id): ?array
    {
        return settings($this->core, 'databases')->get($id);
    }

    /**
     * @param string $driver
     *
     * @return FormInterface|null
     */
    private function getDriverForm(string $driver): ?FormInterface
    {
        $drivers = $this->getDrivers();

        array_unshift(
            $drivers[$driver]['fields'],
            [
                'type' => 'text',
                'name' => 'title',
                'label' => 'Название',
                'default' => ''
            ]
        );
        $form = $this->formBuilder->createFromArray($drivers[$driver]);
        if (!$form) {
            return null;
        }

        $form->addField('hidden', ['name' => 'id']);
        $form->addField('hidden', ['name' => 'id']);
        $form->addField('hidden', ['name' => 'driver', 'value' => $driver]);
        $form->addField('submit', ['name' => '', 'default' => 'Save']);

        return $form;
    }


    /**
     * @return array[]
     * @todo: переделать на формы с валидаторами
     */
    private function getDrivers(): array
    {
        return [
            'mysql' => [
                'type' => 'mysql',
                'name' => 'Mysql',
                'fields' => [
                    [
                        'type' => 'text',
                        'name' => 'host',
                        'label' => 'Host',
                        'default' => 'localhost',
                    ],
                    [
                        'type' => 'text',
                        'name' => 'port',
                        'label' => 'Port',
                        'default' => '3306',
                    ],
                    [
                        'type' => 'text',
                        'name' => 'database',
                        'label' => 'Database',
                        'default' => ''
                    ],
                    [
                        'type' => 'text',
                        'name' => 'username',
                        'label' => 'User',
                        'default' => ''
                    ],
                    [
                        'type' => 'password',
                        'name' => 'password',
                        'label' => 'Password',
                        'default' => ''
                    ],
                    // todo: add types
                    [
                        'type' => 'select',
                        'name' => 'charset',
                        'title' => 'Charset',
                        'default' => 'utf8mb4',
                        'variants' => [
                            'utf8mb4' => 'utf8mb4',
                            'utf8' => 'utf8',
                            'ascii' => 'ascii',
                        ]
                    ],
                    // todo: add types
                    [
                        'type' => 'select',
                        'name' => 'collation',
                        'title' => 'Collation',
                        'default' => 'utf8mb4_general_ci',
                        'variants' => [
                            'utf8mb4_general_ci' => 'utf8mb4_general_ci',
                            'utf8_general_ci' => 'utf8_general_ci',
                            'ascii_general_ci' => 'ascii_general_ci',
                        ]
                    ],

                    [
                        'type' => 'text',
                        'name' => 'prefix',
                        'label' => 'Tables prefix',
                        'default' => ''
                    ]
                ],
            ]
            // todo: add connection types
        ];
    }

}