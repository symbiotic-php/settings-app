<?php

namespace Symbiotic\Apps\Settings\Form;

use Symbiotic\Filesystem\FilesystemManagerInterface;
use Symbiotic\Form\Fields\Select;


class FieldFilesystemSelect extends Select
{
    const TYPE = 'filesystem::filesystem';

    public function __construct(FilesystemManagerInterface $filesystemManager, array $data = [])
    {
        foreach ($filesystemManager->getDisks() as $k => $v) {
            $data['variants'][$k] = $v['name'] ?? \md5(\serialize($v));
        }
        parent::__construct($data);
    }

}