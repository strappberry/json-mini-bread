@php
    $jsonMiniBreadFormField = new \JsonMiniBreadHook\FormFields\JsonMiniBreadFormField();
@endphp

@section('javascript')
    <script>
        $(() => {

            var jsonMiniBreadHelpButton = "{!! trim(addslashes(view('json-mini-bread::tools.bread.help-button'))) !!}";

            function refreshJsonMiniBreadSelects() {
                $('select[name^="field_input_type_"]').each( function ()  {
                    let select = $(this);
                    if (select.val() === "{{ $jsonMiniBreadFormField->getCodename() }}") {
                        let col = select.closest("div");
                        let newButton = $(jsonMiniBreadHelpButton);
                        col.append(newButton);
                        newButton.tooltip();
                    }
                });
            }

            refreshJsonMiniBreadSelects();

            $('select[name^="field_input_type_"]').on('change',function () {
                let select = $(this);
                if (select.val() === "{{ $jsonMiniBreadFormField->getCodename() }}") {
                    let col = select.closest("div");
                    if (col.find('.json-mini-help-button').length === 0) {
                        let newButton = $(jsonMiniBreadHelpButton);
                        col.append(newButton);
                        newButton.tooltip();
                    }
                } else {
                    let col = select.closest("div");
                    if (col.find('.json-mini-help-button').length > 0) {
                        col.find('.json-mini-help-button').remove();
                    }
                }
            });

        });
    </script>
@append
