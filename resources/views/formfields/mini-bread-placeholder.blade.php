<input type="hidden"
       name="{{ $row->field }}"
       data-name="{{ $row->display_name }}"
       value="@if(isset($dataTypeContent->{$row->field})){{ old($row->field, $dataTypeContent->{$row->field}) }}@else{{old($row->field)}}@endif">
<legend class="text-center" style="background-color: #f0f0f0;padding: 5px;">
    @lang("json-mini-bread::generic.input_placeholder_legend", [ 'display_name' => $row->display_name, 'plural_model_name' => $dataType->display_name_plural ])
</legend>
