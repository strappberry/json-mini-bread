<?php

namespace JsonMiniBreadHook\Actions;

use TCG\Voyager\Actions\AbstractAction as VoyagerAbstractAction;

abstract class AbstractAction extends VoyagerAbstractAction
{
    protected $index;

    public function __construct($dataType, $data, $index)
    {
        parent::__construct($dataType, $data);
        $this->index = $index;
    }
}
