<?php

namespace JsonMiniBreadHook\FormFields;

use TCG\Voyager\FormFields\AbstractHandler;

class JsonMiniBreadFormField extends AbstractHandler
{
    protected $codename = "json-mini-bread";

    protected $name = "Json mini BREAD";

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('json-mini-bread::formfields.mini-bread-placeholder', [
            'row' => $row,
            'options' => $options,
            'dataType' => $dataType,
            'dataTypeContent' => $dataTypeContent
        ]);
    }
}
