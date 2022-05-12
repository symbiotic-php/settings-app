<?php

namespace Symbiotic\Apps\Settings\Events;

use Symbiotic\Settings\FieldTypesRepository;
use function _S\view;

class FieldsHandler
{
    public function handle(FieldTypesRepository $repository)
    {
        /** @see FieldTypesRepository::add() phpdoc ***/
        $repository->add('filesystem::filesystem', function (array $field, $value = null): string {
            return view('settings::form/filesystem_field', ['field' => $field, 'value' => $value]);
        });
    }
}