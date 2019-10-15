<?php

namespace JsonMiniBreadHook;

use Illuminate\Support\Str;
use TCG\Voyager\Models\DataRow;

class JsonMiniBreadHelper
{
    /**
     * @var DataRow
     */
    private $dataRow;

    private $fieldDefaults = [
        'type' => 'text',
        'required' => true,
        'visibility' => 'BREAD',
        'details' => [],
    ];

    public function __construct(DataRow $dataRow)
    {
        $dataRow->field_singular = Str::singular($dataRow->field);
        $this->dataRow = $dataRow;
    }

    public function getSingularFieldName()
    {
        return $this->dataRow->field_singular;
    }

    public function allFields()
    {
        $allFields = $this->dataRow->details->fields ?? [];
        $allFields = json_decode(json_encode($allFields), true);
        $allFields = collect($allFields)->map(function ($field) {
            $fixedField = array_merge($this->fieldDefaults, $field);
            $field = $fixedField;
            $field['field'] = kebab_case($field['name']);
            $field['display_name'] = ucfirst($field['name']);
            $field = new DataRow($field);

            return $field;
        });
        return $allFields;
    }

    private function filterAllFields($filter)
    {
        $allFields = $this->allFields();
        $filteredFields = $allFields->filter(function ($field) use ($filter) {
            return Str::contains($field->visibility, $filter);
        });
        return $filteredFields;
    }

    public function browseFields()
    {
        return $this->filterAllFields("B");
    }

    public function readFields()
    {
        return $this->filterAllFields("R");
    }

    public function editFields()
    {
        return $this->filterAllFields("E");
    }

    public function addFields()
    {
        return $this->filterAllFields("A");
    }

    public function deleteFields()
    {
        return $this->filterAllFields("D");
    }

    public function contextField()
    {
        return ($this->dataRow->details->context_field ?? null);
    }
}
