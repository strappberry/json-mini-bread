@can('add', app($dataType->model_name))
<div class="modal modal-success fade" tabindex="-1" id="add_modal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="voyager-plus"></i> {{ __('voyager::generic.add_new') }} {{ strtolower($jsonMiniBreadHelper->getSingularFieldName()) }}</h4>
            </div>
            <form role="form"
                  class="form-edit-add"
                  action="{{ route('voyager.'.$dataType->slug.'.mini.store', [ JsonMiniBreadHook\Facades\JsonMiniBreadHookFacade::getSlugSingular($dataType->slug) => $dataTypeContent->id ]) }}"
                  method="POST" enctype="multipart/form-data">

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

                <!-- Adding -->
                    @php
                        $fields = $jsonMiniBreadHelper->addFields();
                    @endphp

                    @foreach($fields as $field)
                        <div class="form-group @if($field->type == 'hidden') hidden @endif col-md-12">
                            <label for="name">{{ $field->display_name }}</label>
                            {!! app('voyager')->formField($field, (object)[], (object)[]) !!}
                        </div>
                    @endforeach

                </div><!-- modal-body -->

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                </div>
            </form>

            <iframe id="form_target" name="form_target" style="display:none"></iframe>
            <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
                  enctype="multipart/form-data" style="width:0;height:0;overflow:hidden">
                <input name="image" id="upload_file" type="file"
                       onchange="$('#my_form').submit();this.value='';">
                <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
                {{ csrf_field() }}
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endcan
