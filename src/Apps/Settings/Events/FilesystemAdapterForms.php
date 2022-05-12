<?php

namespace Symbiotic\Apps\Settings\Events;

use Symbiotic\Form\FormInterface;

class FilesystemAdapterForms
{
    /**
     * @var array |FormInterface[]
     */
    protected array $adapter_forms = [];

    protected $types = [];
    protected array $drivers_id_generator = [];

    /**
     * @param string $adapter
     * @param FormInterface $form
     * @param string|null $title
     */
    public function add(string $adapter, FormInterface $form, string $title = null, \Closure $generator_id = null)
    {
        $this->adapter_forms[$adapter] = $form;
        if (!$title) {
            $title = ucfirst($adapter);
        }
        $this->types[$adapter] = $title;
        if ($generator_id) {
            $this->drivers_id_generator[$adapter] = $generator_id;
        }
    }

    public function generateId(string $driver, array $data): string
    {
        if (isset($this->drivers_id_generator[$driver])) {
            $func = $this->drivers_id_generator[$driver];
            return $func($data);
        }

        return isset($data['path']) ? \md5($data['path']) : \md5(\serialize($data));
    }

    /**
     * @return array
     */
    public function getFilesystemTypes(): array
    {
        return $this->types;
    }

    /**
     * @param string $adapter
     * @return FormInterface|null
     */
    public function getForm(string $adapter): ?FormInterface
    {
        return isset($this->adapter_forms[$adapter]) ? $this->adapter_forms[$adapter] : null;
    }
}