@can('read', app($dataType->model_name))
<div class="modal modal-success fade" tabindex="-1" id="read_modal_{{ $index }}" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="voyager-eye"></i> {{ __('voyager::generic.viewing') }} {{ strtolower($jsonMiniBreadHelper->getSingularFieldName()) }}</h4>
            </div>
            <div class="modal-body">

                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <!-- form start -->
                    @php
                        $fields = $jsonMiniBreadHelper->browseFields();
                    @endphp

                    @php
                        $contextField = $jsonMiniBreadHelper->contextField();
                    @endphp
                    @if ($dataTypeContent->offsetExists($contextField))
                        <div class="form-group col-md-12">
                            <label for="name">{{ $dataType->display_name_singular }}</label>
                            <h4>{{ $dataTypeContent->{$contextField} }}</h4>
                        </div>
                    @endif

                    @foreach($fields as $row)
                        <div class="panel-heading" style="border-bottom:0;">
                            <h3 class="panel-title">{{ $row->display_name }}</h3>
                        </div>

                        <div class="panel-body" style="padding-top:0;">
                            @if($row->type == "image")
                                <img class="img-responsive"
                                     src="{{ filter_var($data->{$row->field}, FILTER_VALIDATE_URL) ? $data->{$row->field} : Voyager::image($data->{$row->field}) }}">
                            @elseif($row->type == 'multiple_images')
                                @if(json_decode($data->{$row->field}))
                                    @foreach(json_decode($data->{$row->field}) as $file)
                                        <img class="img-responsive"
                                             src="{{ filter_var($file, FILTER_VALIDATE_URL) ? $file : Voyager::image($file) }}">
                                    @endforeach
                                @else
                                    <img class="img-responsive"
                                         src="{{ filter_var($data->{$row->field}, FILTER_VALIDATE_URL) ? $data->{$row->field} : Voyager::image($data->{$row->field}) }}">
                                @endif
                            @elseif($row->type == 'select_dropdown' && property_exists($row->details, 'options') &&
                                    !empty($row->details->options->{$data->{$row->field}})
                            )
                                <?php echo $row->details->options->{$data->{$row->field}};?>
                            @elseif($row->type == 'select_multiple')
                                @if(property_exists($row->details, 'relationship'))

                                    @foreach(json_decode($data->{$row->field}) as $item)
                                        {{ $item->{$row->field}  }}
                                    @endforeach

                                @elseif(property_exists($row->details, 'options'))
                                    @if (count(json_decode($data->{$row->field})) > 0)
                                        @foreach(json_decode($data->{$row->field}) as $item)
                                            @if (@$row->details->options->{$item})
                                                {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}
                                            @endif
                                        @endforeach
                                    @else
                                        {{ __('voyager::generic.none') }}
                                    @endif
                                @endif
                            @elseif($row->type == 'date' || $row->type == 'timestamp')
                                {{ property_exists($row->details, 'format') ? \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($row->details->format) : $data->{$row->field} }}
                            @elseif($row->type == 'checkbox')
                                @if(property_exists($row->details, 'on') && property_exists($row->details, 'off'))
                                    @if($data->{$row->field})
                                        <span class="label label-info">{{ $row->details->on }}</span>
                                    @else
                                        <span class="label label-primary">{{ $row->details->off }}</span>
                                    @endif
                                @else
                                    {{ $data->{$row->field} }}
                                @endif
                            @elseif($row->type == 'color')
                                <span class="badge badge-lg" style="background-color: {{ $data->{$row->field} }}">{{ $data->{$row->field} }}</span>
                            @elseif($row->type == 'coordinates')
                                @include('voyager::partials.coordinates')
                            @elseif($row->type == 'rich_text_box')
                                @include('voyager::multilingual.input-hidden-bread-read')
                                <p>{!! $data->{$row->field} !!}</p>
                            @elseif($row->type == 'file')
                                @if(json_decode($data->{$row->field}))
                                    @foreach(json_decode($data->{$row->field}) as $file)
                                        <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: '' }}">
                                            {{ $file->original_name ?: '' }}
                                        </a>
                                        <br/>
                                    @endforeach
                                @else
                                    <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($row->field) ?: '' }}">
                                        {{ __('voyager::generic.download') }}
                                    </a>
                                @endif
                            @else
                                @include('voyager::multilingual.input-hidden-bread-read')
                                <p>{{ $data->{$row->field} }}</p>
                            @endif
                        </div><!-- panel-body -->
                        @if(!$loop->last)
                            <hr style="margin:0;">
                        @endif
                    @endforeach

                </div>

            </div><!-- modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endcan
