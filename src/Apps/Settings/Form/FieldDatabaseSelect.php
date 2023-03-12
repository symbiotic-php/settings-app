<?php

namespace Symbiotic\Apps\Settings\Form;

use Symbiotic\Database\ConnectionsConfigInterface;
use Symbiotic\Form\Fields\Select;

/**
 * Если стоит пакет symbiotic/database, то поле будет работать
 */
class FieldDatabaseSelect extends Select
{
    const TYPE = 'settings::database';

    /**
     * @param array                           $data
     * @param ConnectionsConfigInterface|null $connectionsConfig
     */
    public function __construct(array $data = [], ConnectionsConfigInterface $connectionsConfig = null)
    {
        if ($connectionsConfig) {
            foreach ($connectionsConfig->getConnections() as $k => $v) {
                $data['variants'][$k] = $v['name'] ?? \md5(\serialize($v));
            }
        }
        parent::__construct($data);
    }

}