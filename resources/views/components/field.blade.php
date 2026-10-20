@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false, 'autocomplete' => null, 'bag' => 'default', 'wire' => null, 'error' => null])
@php
    $errorKey = $wire ?: $name;
    $err = $error ?? $errors->getBag($bag)->first($errorKey);
    $id = 'f-'.preg_replace('/[^a-z0-9]+/i', '-', $name);
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }}@if($required)<span class="text-danger" aria-hidden="true"> *</span>@endif</label>
    @if($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $attributes->get('rows', 4) }}" class="input @if($err) is-invalid @endif"
                  @if($required) required @endif @if($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif
                  @if($wire) wire:model="{{ $wire }}" @endif>{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" class="input @if($err) is-invalid @endif" @if($required) required @endif
                @if($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif @if($wire) wire:model="{{ $wire }}" @endif>
            {{ $slot }}
        </select>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
               @if($type !== 'password') value="{{ old($name, $value) }}" @endif
               class="input @if($err) is-invalid @endif" @if($required) required @endif
               @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
               @if($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif
               @if($wire) wire:model="{{ $wire }}" @endif
               {{ $attributes->except(['class', 'rows']) }}>
    @endif
    @if($err)<p id="{{ $id }}-err" class="field-error" role="alert">{{ $err }}</p>
    @elseif($help)<p class="field-help">{{ $help }}</p>@endif
</div>
