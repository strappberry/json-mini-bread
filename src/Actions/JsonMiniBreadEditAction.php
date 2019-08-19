<?php

namespace JsonMiniBreadHook\Actions;

class JsonMiniBreadEditAction extends AbstractAction
{
    public function getTitle()
    {
        return __('voyager::generic.edit');
    }

    public function getIcon()
    {
        return 'voyager-edit';
    }

    public function getPolicy()
    {
        return 'edit';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary pull-right edit',
            'data-id' => "#edit_modal_{$this->index}",
        ];
    }

    public function getDefaultRoute()
    {
        return 'javascript:;';
    }
}
