<?php

namespace JsonMiniBreadHook\Actions;

use JsonMiniBreadHook\Facades\JsonMiniBreadHookFacade;
use JsonMiniBreadHook\FormFields\JsonMiniBreadFormField;
use TCG\Voyager\Actions\AbstractAction;

class ShowJsonMiniBreadAction extends AbstractAction
{
    public function getTitle()
    {
        $jsonMiniBreadFormField = new JsonMiniBreadFormField();
        $dataRowQuery = $this->dataType->rows()->where('type', $jsonMiniBreadFormField->getCodename());
        if ($dataRowQuery->exists()) {
            return $dataRowQuery->first()->display_name;
        }
        return __('json-mini-bread::generic.json_mini_bread_action');
    }

    public function getIcon()
    {
        return "voyager-window-list";
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-success pull-right edit',
        ];
    }

    public function getDefaultRoute()
    {
        $slug = $this->dataType->slug;
        $slugSingular = JsonMiniBreadHookFacade::getSlugSingular($slug);
        return route("voyager.{$slug}.mini.index", [
            $slugSingular => $this->data->id,
        ]);
    }
}
