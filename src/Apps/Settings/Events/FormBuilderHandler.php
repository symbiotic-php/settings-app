<?php

namespace Symbiotic\Apps\Settings\Events;

use Symbiotic\Apps\Settings\Form\FieldDatabaseSelect;
use Symbiotic\Apps\Settings\Form\FieldFilesystemSelect;
use Symbiotic\Form\FormBuilder;

class FormBuilderHandler
{
    public function handle(FormBuilder $formBuilder)
    {
        /** @see FormBuilder::addType() phpdoc ***/
        $formBuilder->addType(FieldFilesystemSelect::TYPE, FieldFilesystemSelect::class);
        $formBuilder->addType(FieldDatabaseSelect::TYPE, FieldDatabaseSelect::class);
    }
}