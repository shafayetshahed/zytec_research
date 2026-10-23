@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'optional' => false,
    'autocomplete' => null,
])
@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp
<div class="field">
    <label for="{{ $id }}" class="label">{{ $label }}@if ($optional)<span class="label-optional">(optional)</span>@endif</label>
    @if ($hint)
        <p id="{{ $id }}-hint" class="hint">{{ $hint }}</p>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if (! $optional) required aria-required="true" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('id')->merge(['class' => 'input']) }}
    >
    <x-field-error :name="$name" :id="$id.'-error'" />
</div>
