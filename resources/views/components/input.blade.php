@php
$is_input_group = (isset($before) && !empty($before->__toString())) || (isset($after) && !empty($after->__toString()));
@endphp

<div class="form-group">
    @if(!empty($label))
    <label for="{{ $attributes->get('id') }}">
        @if($attributes->has('help'))
            <span class="tip" title="{{ $attributes->get('help') }}">
                {{ $label }} <i class="fa fa-question-circle-o"></i>
            </span>
        @else
            {{ $label }}
        @endif
    </label>
    @endif

    @if($is_input_group)
        <div class="input-group has-feedback">
    @endif

    {{-- Icona prima dell'input --}}
    @if(isset($before) && !empty($before->__toString()))
        {{ $before }}
    @endif

    {{-- Contenuti dell'input --}}
    {{ $slot }}

    {{-- Icona dopo l'input --}}
    @if(isset($after) && !empty($after->__toString()))
        {{ $after }}
    @endif

    @if($is_input_group)
        </div>
    @endif

    @if($attributes->has('help') and $attributes->has('show-help'))
        <span class="help-block pull-left"><small>{{ $attributes->get('help') }}</small></span>
    @endif

    <div id="{{ $unique_id }}-errors"></div>
</div>
