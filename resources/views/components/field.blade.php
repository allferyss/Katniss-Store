@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false, 'bag' => 'default'])
@php
    $errs = $errors->getBag($bag);
    $hasError = $errs->has($name);
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $name);
@endphp
<div>
    <label for="{{ $id }}" class="label">{{ $label }}@if($required) <span class="text-red-400" aria-hidden="true">*</span>@endif</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
           value="{{ $type === 'password' ? '' : old($name, $value) }}"
           {{ $attributes->class(['input', 'input-error' => $hasError]) }}
           @if($required) required @endif
           @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif($hint) aria-describedby="{{ $id }}-hint" @endif>
    @if ($hint && ! $hasError)<p id="{{ $id }}-hint" class="hint">{{ $hint }}</p>@endif
    @if ($hasError)<p id="{{ $id }}-error" class="field-error">{{ $errs->first($name) }}</p>@endif
</div>
