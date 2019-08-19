@can('edit', app($dataType->model_name))
<div class="modal modal-success fade" tabindex="-1" id="edit_modal_{{ $index }}" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="voyager-edit"></i> {{ __('voyager::generic.edit') }} {{ strtolower($jsonMiniBreadHelper->getSingularFieldName()) }}</h4>
            </div>
            <form role="form"
                  class="form-edit-add"
                  action="{{ route('voyager.'.$dataType->slug.'.mini.update', [
            JsonMiniBreadHook\Facades\JsonMiniBreadHookFacade::getSlugSingular($dataType->slug) => $dataTypeContent->id,
            'id' => $index
             ]) }}"
                  method="POST" enctype="multipart/form-data">

                @method('PUT')

                <!-- CSRF TOKEN -->
                {{ csrf_field() }}


                <div class="modal-body">

                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                @php
                    $contextField = $jsonMiniBreadHelper->contextField();
                @endphp
                @if ($dataTypeContent->offsetExists($contextField))
                    <div class="form-group col-md-12">
                        <label for="name">{{ $dataType->display_name_singular }}</label>
                        <h4>{{ $dataTypeContent->{$contextField} }}</h4>
                    </div>
                @endif

                <!-- Editing -->
                    @php
                        $fields = $jsonMiniBreadHelper->editFields();
                    @endphp

                    @foreach($fields as $field)
                        <div class="form-group @if($field->type == 'hidden') hidden @endif col-md-12">
                            <label for="name">{{ $field->display_name }}</label>
                            {!! app('voyager')->formField($field, (object)[], $data) !!}
                        </div>
                    @endforeach

                </div><!-- modal-body -->

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endcan
