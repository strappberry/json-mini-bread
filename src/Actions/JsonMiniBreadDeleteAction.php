<?php

namespace JsonMiniBreadHook\Actions;

class JsonMiniBreadDeleteAction extends AbstractAction
{
    public function getTitle()
    {
        return __('voyager::generic.delete');
    }

    public function getIcon()
    {
        return 'voyager-trash';
    }

    public function getPolicy()
    {
        return 'delete';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-danger pull-right delete',
            'data-id' => $this->index,
            'id'      => "delete-{$this->index}",
        ];
    }

    public function getDefaultRoute()
    {
        return 'javascript:;';
    }
}
