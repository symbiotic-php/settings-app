<?php

namespace Symbiotic\Apps\Settings\Events;

use Symbiotic\Apps\Settings\Http\Form\FieldFilesystemSelect;
use Symbiotic\Form\FormBuilder;

class FilesystemFieldHandler
{
    public function handle(FormBuilder $formBuilder)
    {
        /** @see FormBuilder::addType() phpdoc ***/
        $formBuilder->addType(FieldFilesystemSelect::TYPE, FieldFilesystemSelect::class);
    }
}