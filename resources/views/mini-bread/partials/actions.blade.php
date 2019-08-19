@php $action = new $action($dataType, $data, $index); @endphp

@can($action->getPolicy(), app($dataType->model_name))
    <a href="{{ $action->getRoute($dataType->name) }}" title="{{ $action->getTitle() }}" {!! $action->convertAttributesToHtml() !!}>
        <i class="{{ $action->getIcon() }}"></i> <span class="hidden-xs hidden-sm">{{ $action->getTitle() }}</span>
    </a>
@endcan
