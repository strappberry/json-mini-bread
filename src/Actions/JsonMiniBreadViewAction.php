<?php

namespace JsonMiniBreadHook\Actions;

class JsonMiniBreadViewAction extends AbstractAction
{
    public function getTitle()
    {
        return __('voyager::generic.view');
    }

    public function getIcon()
    {
        return 'voyager-eye';
    }

    public function getPolicy()
    {
        return 'read';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-warning pull-right view',
            'data-id' => "#read_modal_{$this->index}",
        ];
    }

    public function getDefaultRoute()
    {
        return 'javascript:;';
    }
}
