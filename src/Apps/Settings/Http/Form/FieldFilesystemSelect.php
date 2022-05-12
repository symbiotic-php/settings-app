<?php

namespace Symbiotic\Apps\Settings\Http\Form;

use Symbiotic\Form\Fields\Select;
use function _S\core;

class FieldFilesystemSelect extends Select
{
    const TYPE = 'filesystem::filesystem';

    ///protected $template = 'settings::form/filesystem_field';


    public function __construct(array $data = [])
    {
        foreach (core('files')->getDisks() as $k => $v) {
            $data['variants'][$k] = isset($v['name'])?$v['name']: \md5(\serialize($v));
        }
        parent::__construct($data);
    }

}