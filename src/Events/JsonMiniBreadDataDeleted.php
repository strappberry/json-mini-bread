<?php

namespace JsonMiniBreadHook\Events;

use Illuminate\Queue\SerializesModels;
use TCG\Voyager\Events\BreadDataChanged;
use TCG\Voyager\Models\DataType;

class JsonMiniBreadDataDeleted
{
    use SerializesModels;

    public $dataType;

    public $data;

    public function __construct(DataType $dataType, $data)
    {
        $this->dataType = $dataType;

        $this->data = $data;

        event(new JsonMiniBreadDataChanged($dataType, $data, 'Deleted'));
    }
}
