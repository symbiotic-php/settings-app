<?php

namespace Symbiotic\Apps\Settings\Events;

use Symbiotic\Core\CoreInterface;
use Symbiotic\Settings\FieldTypesRepository;
use function _S\view;

class FieldsHandler
{
    public function handle(FieldTypesRepository $repository, CoreInterface $core)
    {
        /** @see FieldTypesRepository::add() phpdoc ***/
        $repository->add('filesystem::filesystem', function (array $field, $value = null)use($core): string {
            return view($core,'settings::form/filesystem_field', ['field' => $field, 'value' => $value]);
        });
    }
}